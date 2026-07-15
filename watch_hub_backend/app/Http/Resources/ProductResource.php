<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'effective_price' => (float) $this->effective_price,
            'is_on_sale' => $this->is_on_sale,
            'stock' => $this->stock,
            'sku' => $this->sku,
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'case_material' => $this->case_material,
            'strap_material' => $this->strap_material,
            'dial_color' => $this->dial_color,
            'movement_type' => $this->movement_type,
            'water_resistance' => $this->water_resistance,
            'case_diameter' => $this->case_diameter,
            'is_featured' => $this->is_featured,
            'is_new_arrival' => $this->is_new_arrival,
            'is_active' => $this->is_active,
            'average_rating' => (float) $this->average_rating,
            'review_count' => $this->review_count,
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
