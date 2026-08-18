<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway\Chile;

use Facturacion\Domain\Port\XmlSigner;

/**
 * Implementacion XAdES-BES enveloped para DTEs de Chile.
 * Genera una firma XMLDSig enveloped sobre el DTE, insertando un nodo
 * <Signature> dentro del root <DTE>, y la agrega: signature value + X509.
 */
final class XadesXmlSigner implements XmlSigner
{
    public function sign(string $xml, string $certificatePem, string $privateKeyPem, ?string $passphrase = null): string
    {
        $pkey = openssl_pkey_get_private(
            'file://' . $privateKeyPem,
            $passphrase !== '' && $passphrase !== null ? $passphrase : null
        );
        if ($pkey === false) {
            throw new \RuntimeException('No se pudo cargar la llave privada del certificado.');
        }

        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = false;
        if (!@$doc->loadXML($xml, LIBXML_NONET)) {
            throw new \RuntimeException('DTE no es XML valido para firmar.');
        }

        // Canonicar la raiz (el nodo Documento) para firmar su contenido.
        $root = $doc->documentElement;
        if ($root === null) {
            throw new \RuntimeException('DTE sin elemento raiz.');
        }
        $canonical = $this->canonicalize($root);

        // Calcular la firma sobre el contenido canonizado.
        $signature = '';
        if (!openssl_sign($canonical, $signature, $pkey, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('No se pudo firmar el DTE.');
        }
        $signatureB64 = base64_encode($signature);

        // Crear el nodo Signature (XMLDSig) enveloped.
        $ns = 'http://www.w3.org/2000/09/xmldsig#';
        $sig = $doc->createElementNS($ns, 'Signature');
        $sig->setAttribute('xmlns', $ns);

        $signedInfo = $doc->createElement('SignedInfo');
        $canon = $doc->createElement('CanonicalizationMethod');
        $canon->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $signedInfo->appendChild($canon);

        $sigMeth = $doc->createElement('SignatureMethod');
        $sigMeth->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256');
        $signedInfo->appendChild($sigMeth);

        $ref = $doc->createElement('Reference');
        $ref->setAttribute('URI', '');
        $transforms = $doc->createElement('Transforms');
        foreach (['http://www.w3.org/2000/09/xmldsig#enveloped-signature', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315'] as $alg) {
            $t = $doc->createElement('Transform');
            $t->setAttribute('Algorithm', $alg);
            $transforms->appendChild($t);
        }
        $ref->appendChild($transforms);

        $digestMeth = $doc->createElement('DigestMethod');
        $digestMeth->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $ref->appendChild($digestMeth);

        $digestValue = $doc->createElement('DigestValue', base64_encode(hash('sha256', $canonical, true)));
        $ref->appendChild($digestValue);
        $signedInfo->appendChild($ref);

        $sig->appendChild($signedInfo);

        $sigValue = $doc->createElement('SignatureValue', $signatureB64);
        $sig->appendChild($sigValue);

        // Incrustar el certificado X509.
        $x509Data = $doc->createElement('KeyInfo');
        $x509Data->appendChild($doc->createElement('X509Data'));
        $x509 = $x509Data->getElementsByTagName('X509Data')->item(0);
        $certContents = (string) file_get_contents($certificatePem);
        $x509->appendChild($doc->createElement('X509Certificate', $this->stripPem($certContents)));
        $x509Data = $doc->getElementsByTagName('KeyInfo')->item(0);
        $sig->appendChild($x509Data);

        // Insertar la firma al final del root DTE.
        $root->appendChild($sig);

        return $doc->saveXML();
    }

    private function canonicalize(\DOMElement $node): string
    {
        $doc = new \DOMDocument();
        $imported = $doc->importNode($node, true);
        $doc->appendChild($imported);
        return $doc->C14N(true, false);
    }

    private function stripPem(string $pem): string
    {
        $pem = str_replace("-----BEGIN CERTIFICATE-----", '', $pem);
        $pem = str_replace("-----END CERTIFICATE-----", '', $pem);
        return preg_replace('/\s+/', '', $pem) ?? '';
    }
}