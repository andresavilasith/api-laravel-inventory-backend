<?php

namespace App\Helpers;

use App\Models\Inventory\Product;
use App\Models\Inventory\Tax;

class OutgoingManage
{
    public static function subtractProducts($products)
    {
        foreach ($products as $productValue) {
            $product = Product::find($productValue['product_id']);
            $productQuantity = $productValue['quantity'];
            $product->sales  = $product->sales + $productQuantity;
            $product->stock  = $product->stock - $productQuantity;
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

            $product->sales  = $product->sales - $oldProducts[$key]['quantity'] + $productQuantity;

            if ($productQuantity > $oldProducts[$key]['quantity']) {
                $product->stock  =  $product->stock -  ($productQuantity - $oldProducts[$key]['quantity']);
            } else {

                $product->stock  =  $product->stock  + ($oldProducts[$key]['quantity'] - $productQuantity);
            }

            $product->update();
        }

        //Modify stock and sales if the product is remove from the list of products 
        foreach ($oldProducts as $key => $oldProductValue) {
            $oldProductId = $oldProductValue['product_id'];
            $quantityOldProduct = $oldProductValue['quantity'];

            if (!in_array($oldProductId, $updateProductsIds)) {
                $product = Product::find($oldProductId);
                $product->stock = $product->stock + $quantityOldProduct;
                $product->sales = $product->sales - $quantityOldProduct;
                $product->update();
            }
        }
    }

    public static function removeProducts($products)
    {
        foreach ($products as $productValue) {
            $product = Product::find($productValue['product_id']);
            $productQuantity = $productValue['quantity'];
            $product->sales  = $product->sales - $productQuantity;
            $product->stock  = $product->stock + $productQuantity;
            $product->update();
        }
    }

    public static function calculateTotalWithTaxes($outgoingRequest)
    {
        $products = $outgoingRequest['products'];
        $isEqual = false;
        $subtotalQuantityPrice = [];
        $taxQuantityPrice = [];
        $subtotal = 0;


        foreach ($products as  $product) {
            $tax_id = Product::find($product['product_id'])->tax_id;

            $percentage = Tax::find($tax_id)->percentage;

            $quantity = $product['quantity'];
            $price = $product['price'];

            $totalTax = $price * $percentage / 100;

            $taxQuantityPrice[] = $totalTax * $quantity;

            $subtotalQuantityPrice[] = $quantity * $price;
        }
        $requestSubtotal = round($outgoingRequest['subtotal'], 2);
        $requestTaxes = round($outgoingRequest['taxes'], 2);
        $requestTotal = round($outgoingRequest['total'], 2);

        $subtotal = round(array_sum($subtotalQuantityPrice), 2);
        $taxes = round(array_sum($taxQuantityPrice), 2);
        $total = round($subtotal + $taxes, 2);


        if ($requestSubtotal === $subtotal && $requestTaxes === $taxes && $requestTotal === $total) {
            $isEqual = true;
        }

        return $isEqual;
    }
}
