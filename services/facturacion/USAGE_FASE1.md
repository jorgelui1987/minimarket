# Fase 1 — Perú / SUNAT (funcional)

El adaptador `PeruSunatGateway` ya emite comprobantes reales usando **Greenter**
(genera UBL 2.1, firma XAdES-BES y envía a SUNAT/OSE; lee el CDR). Nuestra capa
mapea el modelo canónico, clasifica la respuesta y persiste los artefactos.

## 1. Instalar dependencias

```bash
cd services/facturacion
composer install      # instala greenter/greenter y greenter/report
```

Requiere las extensiones PHP: `openssl`, `soap`, `zip`, `dom` (todas estándar en
XAMPP/Laragon). Verifica con `php -m`.

## 2. Certificado digital

SUNAT firma con un certificado en formato **PEM** (certificado público + llave
privada concatenados en un mismo archivo).

- **Homologación (beta):** usa el certificado demo de SUNAT/Greenter. Colócalo en
  `storage/facturacion/pe/certificate.pem`.
- **Producción:** convierte tu `.pfx`/`.p12` a PEM:

  ```bash
  openssl pkcs12 -in certificado.pfx -out certificate.pem -nodes
  ```

## 3. Credenciales (Clave SOL)

| Entorno | RUC | Usuario | Clave |
|---------|-----|---------|-------|
| Beta (homologación) | 20000000001 | MODDATOS | MODDATOS |
| Producción | tu RUC | usuario secundario | su clave |

> Recomendado: crear un **usuario secundario** SOL con permiso de facturación
> electrónica, no la clave del RUC principal.

Configúralas en `config/facturacion.php` (bloque `PE`) o por variables de
entorno (`PE_RUC`, `PE_SOL_USER`, `PE_SOL_PASS`, `PE_CERT_PATH`, `PE_MODE`).

## 4. Probar en beta

```bash
php examples/emitir_factura_beta.php
```

Salida esperada (aceptado):

```
[audit] signed        20000000001-01-F001-1 {"xml":"PE/20000000001/..."}
[audit] transmitted   20000000001-01-F001-1 {"attempt":1}
== RESULTADO ==
Estado:   aceptado
Externo:  20000000001-01-F001-1
XML:      PE/20000000001/20000000001-01-F001-1.xml
CDR:      PE/20000000001/R-20000000001-01-F001-1.zip
Codigo:   0 - La Factura numero F001-1, ha sido aceptada
```

## 5. Qué hace cada pieza

| Componente | Responsabilidad |
|------------|-----------------|
| `SeeFactory` | Construye el cliente Greenter (certificado, Clave SOL, endpoint) |
| `CanonicalToGreenterMapper` | Traduce el modelo canónico a Invoice/Note/Voided de Greenter (IGV 18%, afectación, leyendas) |
| `NumeroALetras` | Leyenda obligatoria (código 1000) del monto en letras |
| `PeruSunatGateway` | Firma, envía con reintentos, clasifica CDR → estado del dominio, persiste XML/CDR |
| `FilesystemDocumentStorage` | Guarda XML firmado y CDR (retención legal) |
| `HtmlInvoiceRenderer` | Representación impresa (HTML → PDF con dompdf/greenter-report) |

## 6. Mapeo de respuesta SUNAT → estado

| CDR / error | Estado del dominio | ¿Reintenta? |
|-------------|--------------------|-------------|
| Código 0, sin notas | `aceptado` | — |
| Código 0, con notas | `observado` | — |
| Código 2000–3999 | `rechazado` (corregir) | No |
| Código 4000+ | `observado` | — |
| Excepción < 2000 / timeout / SOAP fault de red | `error` | Sí (backoff) |

## 7. Anulación

- **Facturas:** `anularFactura()` genera una **Comunicación de Baja** y devuelve un
  *ticket*; el estado final se confirma con `consultarEstado()` (que llama a
  `getStatus($ticket)`).
- **Boletas / devoluciones parciales:** usar **Nota de Crédito**
  (`emitirNotaCredito()`).

## 8. Pendiente para producción

- Certificado y credenciales reales; cambiar `PE_MODE=produccion`.
- `getStatusCdr` para consultar comprobantes individuales (servicio de consulta
  con credenciales adicionales).
- PDF: enganchar `HtmlInvoiceRenderer` con dompdf o `greenter/report` + QR.
- Persistencia real (`DocumentRepository` con Eloquent) y colas — **Fase 2**.
