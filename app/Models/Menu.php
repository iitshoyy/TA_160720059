<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model {
    protected $fillable = ['name','description','availability','price','categoryMenus_id','image'];
    public function category()      { return $this->belongsTo(CategoryMenu::class,'categoryMenus_id'); }
    public function orderDetails()  { return $this->hasMany(OrderDetail::class,'menus_id'); }
    public function components()    { return $this->hasMany(Component::class,'menus_id'); }

    /**
     * Max portions makeable from current stock.
     * No recipe => 0 (unavailable). Otherwise the bottleneck ingredient:
     * min over components of floor(quantity_on_hand / per-portion quantity).
     */
    public function stockCapacity(): int
    {
        $this->loadMissing('components.ingridient.inventories');
        if ($this->components->isEmpty()) {
            return 0;
        }
        $caps = [];
        foreach ($this->components as $comp) {
            $per = (float) $comp->quantity;
            if ($per <= 0) {
                continue;
            }
            $onHand = (float) ($comp->ingridient?->inventories->first()->quantity_on_hand ?? 0);
            $caps[] = (int) floor($onHand / $per);
        }
        return empty($caps) ? 0 : max(0, min($caps));
    }

    public function isSoldOut(): bool
    {
        return $this->stockCapacity() === 0;
    }
}
