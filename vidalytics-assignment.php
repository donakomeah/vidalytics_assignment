<?php

interface Product
{
	 public function addProduct(string $product_code):void;
	 public function getProduct(string $product_code):ProductItem;
	 public function loadProductCatalogue(array $product_catalog):void;
}

interface DeliveryRule
{
	public function getDeliveryCost():float;
	public function getZeroDeliveryCost():float;
	public function setDeliveryCostBand(array $cost_band):void;
}

interface Offers
{
	public function isEligibleForOffer(string $product_code):bool;
	public function addSpecialOfferProduct(string $product_code):void;
	public function removeSpecialOfferProduct(string $product_code):void;
}

//Class to map total cost of items with its associated delivery cost
class DeliveryCostBand
{
	private $total_cost_value;
	private $delivery_cost;
	public function __construct(float $total_cost, float $delivery_cost)
	{
		$this->total_cost_value = $total_cost;
		$this->delivery_cost = $delivery_cost;
	}
	
	public function getTotalCostValue()
	{
		return $this->total_cost_value;
	}
		
	public function getDeliveryCost()
	{
		return $this->delivery_cost;
	}
	//Comparism function for sorting by total_cost_value
	static function cmp_obj($a, $b)
    {
        $al = strtolower($a->total_cost_value);
        $bl = strtolower($b->total_cost_value);
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }
}

//Class to contain all properties of a product
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
		$this->product_price = $price;
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

//Basket to hold all purchased items and determine total value of Items as well as the delivery cost
class Basket implements Product, DeliveryRule,Offers
{
	private $product_catalogue = array(); //An array of all products available for purchase
	public $special_offer_eligible_codes=array(); //an array of product codes eligible for special offers
	public $delivery_pricing=array();  //an array of different price ranges and their corresponding delivery costs
	private $delivery_cost; //To store value of computed cost of delivery
	private $bag=array(); //holds an array of bought items
	private $zero_delivery=0;
	const SPECIAL_OFFER_PERCENTAGE = 50;
	const DEFAULT_ZERO_DELIVERY_RATE = 200;
	
	public function __construct($special_offer_product_codes,array $delivery_cost_rules, array $product_catalogue){
		$this->special_offer_eligible_codes = $special_offer_product_codes;
		$this->setDeliveryCostBand($delivery_cost_rules);
		$this->loadProductCatalogue($product_catalogue);
	}
	
	public function getZeroDeliveryCost():float
	{
		$v=0;
		if(count($this->delivery_pricing) > 0)
		{
			foreach($this->delivery_pricing as $dp)
			{
				if($dp->getTotalCostValue() > $v)
					$v = $dp->getTotalCostValue();
			}
		}
		return $v == 0 ? self::DEFAULT_ZERO_DELIVERY_RATE : $v;
	}
	
	public function loadProductCatalogue(array $product_catalog):void
	{
		if(count($product_catalog) > 0)
		{
			$this->product_catalogue = $product_catalog;
		}
	}
	public function setDeliveryCostBand(array $d):void
	{
		if(count($d) > 0){
			$this->delivery_pricing = $d;
		}
	}
		
	public function isEligibleForOffer($product_code):bool
	{
		if(in_array($product_code,array_values($this->special_offer_eligible_codes)))
			return true;
		else
			return false;
	}
	public function addSpecialOfferProduct($product_code):void
	{
		if(!in_array($product_code, array_values($this->special_offer_eligible_codes)))
		{
			$this->special_offer_eligible_codes[] = $product_code;
		}
	}
	
	public function removeSpecialOfferProduct($product_code):void
	{
		if(in_array($product_code, array_values($this->special_offer_eligible_codes)))
		{
			$key = array_search($product_code, $this->special_offer_eligible_codes);
			unset($this->special_offer_eligible_codes[$key]);
		}
	}
	
	public function countProducts($product_code)
	{
		$counter=0;
		foreach($this->bag as $p)
		{
			if($product_code == $p->getProductCode())
				$counter++;		
		}
		return $counter;
	}
	
	public function getTotalCostPrice()
	{
		$total_cost=0;
		$counter=0;
		$eligible_items=array();
		foreach($this->bag as $p)
		{
			$total_cost += $p->getProductPrice();
		}
		return $total_cost;
	}
	
	public function getDeliveryCost():float
	{
		if($this->getTotalCostPrice() > $this->getZeroDeliveryCost())
			$this->delivery_cost = 0;
		else{
			foreach($this->delivery_pricing as $band)
			{
				if($this->getTotalCostPrice() < $band->getTotalCostValue())
				{
					$this->delivery_cost = $band->getDeliveryCost();
					break;
				}
			}
		}
		return $this->delivery_cost;
	}
	
	public function getTotal()
	{
		return $this->getTotalCostPrice() + $this->getDeliveryCost();
	}
	
	public function getProduct($product_code):ProductItem
	{
		foreach($this->product_catalogue as $pc)
		{
			if($pc->getProductCode()  == $product_code)
			{
				return $pc;
				break;
			}
		}
		return null;
	}
	
	public function addProduct($product_code):void
	{
		if($this->getProduct($product_code) != null)
		{
			if($this->isEligibleForOffer($product_code)){
				if($this->countProducts($product_code) == 1 || $this->countProducts($product_code) % 2 == 1)
				{	
					$p = $this->getProduct($product_code);
					$product_price_adjusted = new ProductItem($p->getProductCode(),$p->getProductName(),$p->getProductPrice()*(self::SPECIAL_OFFER_PERCENTAGE/100));
					$this->bag[] = $product_price_adjusted;
				}else{
					$this->bag[] = $this->getProduct($product_code);
				}
			}else{
				$this->bag[] = $this->getProduct($product_code);
			}
		}
	}
	
	
}


//Class Implementation

//Product Catalogues
$p = new ProductItem('R01','RED WIDGET',32.95);
$p1 = new ProductItem('G01','GREEN WIDGET',24.95);
$p2 = new ProductItem('B01','BLUE WIDGET',7.95);
$product_catalogue = array($p, $p1,$p2);

//Deloivery Cost rules
$d = new DeliveryCostBand(50,4.95);
$d1 = new DeliveryCostBand(90,2.95);
$delivery_rules = array($d, $d1);


//An array of products on Special Offers
$special_offer = array('R01','G01');

//Initializing Basket Object
//special offers, delivery_rule, product_catalogue
$b = new Basket($special_offer, $delivery_rules, $product_catalogue);


$b->addProduct('R01');
$b->addProduct('R01');
$b->addProduct('B01');
$b->addProduct('B01');
$b->addProduct('R01');


//Add product B01 as a product on special offer
$b->addSpecialOfferProduct('B01');


echo 'Items Cost is: '.$b->getTotalCostPrice()."<br />";
echo 'Delivery Cost is: '.$b->getDeliveryCost()."<br />";
echo 'Total Price + Delivery Cost is: '.$b->getTotal()."<br />";

//Remove product R01 from array on products on special offer
$b->removeSpecialOfferProduct('R01');

