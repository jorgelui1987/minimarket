<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class StockMovement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'product_id', 'user_id', 'type',
        'quantity', 'stock_after', 'source', 'source_id', 'note',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Registra un movimiento de inventario. Debe llamarse DESPUÉS de aplicar
     * el cambio de stock al producto (para que $product->stock ya esté actualizado).
     *
     * @param  int  $change  Cambio con signo: positivo entra, negativo sale.
     */
    public static function record(Product $product, int $change, string $type, ?string $source = null, ?int $sourceId = null, ?string $note = null): self
    {
        return static::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'type' => $type,
            'quantity' => $change,
            'stock_after' => (int) $product->stock,
            'source' => $source,
            'source_id' => $sourceId,
            'note' => $note,
        ]);
    }
}
