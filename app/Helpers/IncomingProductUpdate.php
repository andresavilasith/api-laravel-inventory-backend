<?php

namespace App\Helpers;

use App\Models\Inventory\Product;

class IncomingProductUpdate
{
    public static function addProducts($products)
    {
        foreach ($products as $productValue) {
            $product = Product::find($productValue['product_id']);
            $productQuantity = $productValue['quantity'];
            $product->receipts  = $product->receipts + $productQuantity;
            $product->stock  = $product->stock + $productQuantity;
            $product->update();
        }
    }

    public static function updateProducts($oldProducts, $products)
    {
        $updateProductsIds = [];

        foreach ($products as $key => $productValue) {
            $updateProductsIds[] = $productValue['product_id'];
            $product = Product::find($productValue['product_id']);
            $productQuantity = $productValue['quantity'];
            $product->receipts  = $product->receipts - $oldProducts[$key]['quantity'] + $productQuantity;
            $product->stock  =  $product->stock - $oldProducts[$key]['quantity'] + $productQuantity;
            $product->update();
        }

        //Modify stock and receipts if the product is remove from the list of products 
        foreach ($oldProducts as $key => $oldProductValue) {
            $oldProductId = $oldProductValue['product_id'];
            $quantityOldProduct = $oldProductValue['quantity'];
            if (!in_array($oldProductId, $updateProductsIds)) {
                $product = Product::find($oldProductId);
                $product->stock = $product->stock - $quantityOldProduct;
                $product->receipts = $product->receipts - $quantityOldProduct;
                $product->update();
            };
        }
    }

    public static function removeProducts($products)
    {
        foreach ($products as $productValue) {
            $product = Product::find($productValue['product_id']);
            $productQuantity = $productValue['quantity'];
            $product->receipts  = $product->receipts - $productQuantity;
            $product->stock  = $product->stock - $productQuantity;
            $product->update();
        }
    }
}
