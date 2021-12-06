<?php

interface Product
{
	 public function addProduct(ProductItem $product):void;
}

interface DeliveryRule
{
	public function getDeliveryCost():float;
}

interface Offers
{
	public function isEligibleForOffer($product_code):bool;
}

class ProductItem
{
	private $product_code;
	private $product_name;
	private $product_price;
	
	public function __construct($product_code, $product_name, $product_price)
	{
		$this->product_code = $product_code;
		$this->product_name = $product_name;
		$this->product_price = $product_price;
	}
	

	public function getProductCode():string
	{
		return $this->product_code;
	}
	/*return product name */
	public function getProductName():string
	{
		return $this->product_name;
	}
	/*return product price */
	public function getProductPrice()
	{
		return $this->product_price;
	}
	public function setProductPrice($price){
		$this->peoduct_price = $price;
	}
	
	//Comparism function for sorting by product_code
	static function cmp_obj($a, $b)
    {
        $al = strtolower($a->product_code);
        $bl = strtolower($b->product_code);
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }
}
class Basket implements Product, DeliveryRule,Offers
{

	public $special_offer_eligible_codes=array();
	private ProductItem $product_item;
	private $delivery_cost;
	private $bag=array();
	
	//get a list of product codes separated by comma that are eligible for special offer
	//it is assumed that special offer could be given on more than 1 item
	//It is assumed that upon initializing a basket, the product for special offer is added by product code and stored in a special variable
	public function __construct($product_codes){
		
		$this->special_offer_eligible_codes = explode(',',$product_codes);
	}
	
	public function isEligibleForOffer($product_code):bool
	{
		if(in_array($product_code,array_values($this->special_offer_eligible_codes)))
			return true;
		else
			return false;
	}
	
	public function getTotalCostPrice()
	{
		//Sort Items in the bag by product_code
		//It is  important to sort by product code because if multiple items are given for special offer
		//and items are not placed in the basket in any sequential order algorithm computing special offer 
		//prices will not properly keep track of item counted for special offer.
		usort($this->bag, array("ProductItem", "cmp_obj"));
		
		$total_cost=0;
		$counter=0;
		$eligible_items=array();
		foreach($this->bag as $p)
		{
			if($this->isEligibleForOffer($p->getProductCode())){
				$counter++;
			}
			
			if($counter == 2)
			{
				$total_cost += ($p->getProductPrice()/2);
				$counter=0;
			}else{
				$total_cost += $p->getProductPrice();
			}
		}
		return $total_cost;
	}
	
	public function getDeliveryCost():float
	{
		switch (true)
		{
			case $this->getTotalCostPrice() < 50:
				$this->delivery_cost = 4.95;
				break;
			case $this->getTotalCostPrice() < 90:
				$this->delivery_cost = 2.95;
				break;
			default:
				$this->delivery_cost = 0;
				break;
		}
		return $this->delivery_cost;
	}
	
	public function getTotal()
	{
		return $this->getTotalCostPrice() + $this->getDeliveryCost();
	}
	
	
	public function addProduct(ProductItem $product):void
	{
		$this->bag[] = $product;
	}
}

$p = new ProductItem('R01','RED WIDGET',32.95);
$p1 = new ProductItem('G01','GREEN WIDGET',24.95);
$p2 = new ProductItem('B01','BLUE WIDGET',7.95);


$b = new Basket('R01','B01');
$b->addProduct($p2);
$b->addProduct($p);
$b->addProduct($p1);
$b->addProduct($p2);
$b->addProduct($p);
$b->addProduct($p2);
$b->addProduct($p);
$b->addProduct($p);
$b->addProduct($p2);

echo 'Items Cost is: '.$b->getTotalCostPrice()."<br />";
echo 'Delivery Cost is: '.$b->getDeliveryCost()."<br />";
echo 'Total Price + Delivery Cost is: '.$b->getTotal()."<br />";


