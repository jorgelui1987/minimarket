# Activar la facturación electrónica en el ERP

El POS ya emite el comprobante tras cada venta. Falta elegir **cómo** emite
(driver) y correr la migración.

## 1. Migración (una vez)

```bash
php artisan migrate       # crea la tabla electronic_documents
```

## 2. Elegir driver en `.env`

| Driver | Qué hace | Cuándo usarlo |
|--------|----------|---------------|
| `null` (default) | No emite; deja el comprobante en *pendiente* | Mientras configuras |
| `local` | Emite **in-process** con Greenter (sin servidor aparte) | Recomendado "junto al proyecto" |
| `rest` | Llama a un servicio HTTP externo | Si despliegas el servicio aparte |

### Opción recomendada: driver `local`

```env
BILLING_ENABLED=true
BILLING_DRIVER=local

# SUNAT beta (homologación)
PE_MODE=beta
PE_RUC=20000000001
PE_SOL_USER=MODDATOS
PE_SOL_PASS=MODDATOS
PE_CERT_PATH=storage/facturacion/pe/certificate.pem
PE_RAZON_SOCIAL="MINIMARKET DEMO S.A.C."
```

Instala las dependencias fiscales (Greenter) en el ERP y coloca el certificado:

```bash
composer update          # instala greenter/greenter y autoloader de Facturacion\
mkdir -p storage/facturacion/pe
# copia el certificado demo PEM en storage/facturacion/pe/certificate.pem
```

Listo. Al cerrar una boleta/factura en el POS, el comprobante se firma, se envía
a SUNAT beta y el estado (aceptado/observado/rechazado) aparece en la pantalla de
la venta, con descarga de XML y CDR.

### Opción `rest` (servicio desplegado aparte)

```env
BILLING_ENABLED=true
BILLING_DRIVER=rest
BILLING_SERVICE_URL=http://localhost:8090
BILLING_SERVICE_TOKEN=xxxxx
```

## 3. Producción

- `PE_MODE=produccion`, RUC real, usuario secundario SOL y certificado real
  (convertir `.pfx`→PEM: `openssl pkcs12 -in cert.pfx -out certificate.pem -nodes`).
- Ver `services/facturacion/USAGE_FASE1.md` para el detalle de SUNAT.

## Cómo funciona (resumen)

```
POS (venta)
  └─ BillingService.emitForSale()   crea/actualiza ElectronicDocument
       └─ BillingClient (según driver)
            ├─ LocalBillingClient  → PeruSunatGateway (Greenter) → SUNAT
            ├─ RestBillingClient   → servicio HTTP → SUNAT
            └─ NullBillingClient   → 'pendiente'
```

El ERP nunca conoce SUNAT: sólo habla con `BillingClient`. Cambiar de país o de
transporte no toca el POS.
