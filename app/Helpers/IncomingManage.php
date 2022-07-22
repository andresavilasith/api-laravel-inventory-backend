<?php

namespace App\Helpers;

use App\Models\Inventory\Product;

class IncomingManage
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
            $countOldProducts = count($oldProducts);

            if ($key <= $countOldProducts - 1 && $product->id == $oldProducts[$key]['product_id']) {
                $product->stock  =  $product->stock - $oldProducts[$key]['quantity'] +  $productQuantity;
                $product->receipts  = $product->receipts - $oldProducts[$key]['quantity'] +  $productQuantity;
            } else {
                $product->stock  =  $product->stock +  $productQuantity;
                $product->receipts  = $product->receipts +  $productQuantity;
            }

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

    public static function calculateTotal($incomingRequest)
    {
        $products = $incomingRequest['products'];
        $isEqual = false;
        $totalQuantityPrice = [];
        $total = 0;


        foreach ($products as  $product) {
            $totalQuantityPrice[] = $product['quantity'] * $product['price'];
        }

        $total = array_sum($totalQuantityPrice);
        //dd($total);

        if ($incomingRequest['total'] === $total) {
            $isEqual = true;
        }

        return $isEqual;
    }
}
