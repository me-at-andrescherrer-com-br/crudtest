<?php

namespace App\Transformers;

use App\Product;
use Flugg\Responder\Transformers\Transformer;

class UserProductsTransformer extends Transformer
{
    /**
     * List of available relations.
     *
     * @var string[]
     */
    protected $relations = [];

    /**
     * List of autoloaded default relations.
     *
     * @var array
     */
    protected $load = [];

    /**
     * Transform the model.
     *
     * @param  \App\Product $product
     * @return array
     */
    public function transform(Product $product)
    {
        return [
            'id' => (int) $product->id,
            'name'  => (string) $product->name,
            'price' => number_format($product->price, 2),
            'weight' => number_format($product->weight, 2),
            'picture' => $product->picture,
        ];
    }
}
