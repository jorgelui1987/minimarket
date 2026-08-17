# Servicio de Facturación Electrónica (multi-país)

Servicio independiente que emite comprobantes fiscales para el ERP Minimarket.
Soporta **Perú (SUNAT)** de inicio y está diseñado para incorporar
**Colombia (DIAN), Chile (SII), Argentina (ARCA) y México (SAT)** sin modificar
la lógica del ERP ni la de los países ya implementados.

## Principios

- **Clean Architecture + DDD**: dominio puro en el centro, adaptadores en el borde.
- **Strategy / Adapter por país**: una interfaz común (`FiscalGateway`) con
  cuatro operaciones — `emitirFactura`, `emitirNotaCredito`, `anularFactura`,
  `consultarEstado` — implementada una vez por país.
- **Open/Closed**: agregar un país = **una clase nueva + una entrada en
  `config/facturacion.php`**. No se toca código existente.
- **Modelo canónico**: el ERP envía un documento agnóstico; cada adaptador lo
  traduce a UBL 2.1 / DTE / CFDI 4.0 / WSFEv1.

## Estructura

```
src/
  Domain/            # nucleo agnostico de pais (modelo, puertos, excepciones)
    Model/           # CanonicalDocument, Party, Money, Tax, enums...
    Port/            # FiscalGateway (Strategy), DocumentRepository, XmlSigner...
    Result/          # EmissionResult, AnnulmentResult, StatusResult
    Exception/       # Validation / BusinessRejection / TransientTransmission
  Application/       # casos de uso + FiscalGatewayFactory (resuelve el pais)
    UseCase/         # EmitirFactura, EmitirNotaCredito, AnularFactura, ConsultarEstado
  Infrastructure/    # adaptadores concretos
    Gateway/Peru/    # SUNAT (referencia, end-to-end)
    Gateway/Colombia # DIAN  (Fase 3)
    Gateway/Chile/   # SII   (Fase 4, con gestor de folios CAF)
    Gateway/Argentina# ARCA  (Fase 5, SOAP/CAE)
    Gateway/Mexico/  # SAT   (Fase 6, CFDI 4.0/PAC)
  Interface/Http/    # API REST que consume el ERP
config/facturacion.php   # REGISTRO de paises (punto unico de extension)
```

## Cómo agregar un país (ejemplo: Colombia)

1. Implementar `Infrastructure/Gateway/Colombia/ColombiaDianGateway` (implements `FiscalGateway`).
2. Aislar el XML local en un builder propio (`Ubl21DianBuilder`).
3. Registrar en `config/facturacion.php` con `enabled => true`.
4. Nada más. El ERP, los use cases y los otros países no cambian.

## Comunicación con el ERP

REST síncrono para la solicitud + procesamiento asíncrono interno (cola con
reintentos). El ERP usa `App\Services\Billing\BillingClient` (ver
`app/Services/Billing`) y nunca habla directamente con la autoridad fiscal.

Cabecera `Idempotency-Key` obligatoria en emisión para evitar duplicados.

## Diferencias por país (resumen)

| País | Autoridad | Formato | Identificador | Modelo | Transporte |
|------|-----------|---------|---------------|--------|------------|
| PE | SUNAT | UBL 2.1 | CDR / nombre | post-emisión (OSE/PSE) | SOAP/REST |
| CO | DIAN | UBL 2.1 | CUFE / CUDE | validación previa | SOAP |
| CL | SII | DTE | folio (CAF) | folios CAF | SOAP |
| AR | ARCA | WSFEv1 | CAE | pre-clearance | SOAP |
| MX | SAT | CFDI 4.0 | UUID | timbrado PAC | REST/SOAP (PAC) |

> Datos verificados a jul-2026. AFIP pasó a **ARCA** en oct-2024; en México la
> única versión vigente es **CFDI 4.0**.
