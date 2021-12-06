### Basket Class
The **'Basket'** class is for imitating shopping cart as indicated in the task. This class Implements a number of interfaces. The Product, Offers and DeliveryRule interfaces.
Upon initialization of the Object the class accepts an array of product codes which are eligible for special offer as the first parameter. 
It is assumed that multiple items could be selected for a special offer. The constructor also 
takes an array of the delivery rules as well as the product catalogue which is an array of ProductItem class.

### Product Interface

There is a addProduct method to add product items which updates the bag array. The bag array contains products selected for purchase. Add Product method accepts product code as parameter.
The add product method checks if item is eligible for special offer and amends the product price if eligible. It access the Product Item object as a parameter.
This interface also has the getProduct method for retrieving a object of ProductItem class from the product catalogue by passing product code as parameter.
Product interface also has the loadProductCatalogue method with loads an array of ProductItem to populate the product catalogue property in the Basket class. 
The product catalogue contains information about all products available on sale.
 
### Offers Interface

This interface has 3 methods. The first being isEligible method to check if a particular product is eligible for special offer. 
The addSpecialOfferProduct can also be used for adding products on special offer, whiles removeSpecialOfferProduct method is used 
for removing products on speical offer.

### DeliveryRules Interface

This interface has 3 methods. The getDeliveryCost method is used to estimate total cost of delivery based on value of the total cost of items in the basket.
The getZeroDeliveryCost method is used to get total cost value based on delivery rules. It is assumed if the total value of items purchased is higher than the highest value 
defined in the delivery rule. This is selected as the value for zero delivery cost. A default value is assigned if no delivery rule is available.
The setDeliveryCostBand is used for configuring different bands of value of total products purchased and their delivery cost. It accepts the DeliveryCostBand object as 
a parameter. It is also assumed that the setDeliveryCostBand method can be used to adjust the zero delivery rate if a total cost value in a defined cost band is greater than 
the existing zero delivery rate.


The Basket also has some other methods. 
countProducts method is used to count number of occurrences of a particular product in the basket.
getTotalCostPrice method to calculate cost price of all items in the bag.
getTotal method is the sum of getTotalCostPrice and getgetDeliveryCost method

#### ProductItem 
ProductItem class defines all properties about a product. The property values are set upon initialization.

#### DeliveryCostBand
DeliveryCostBand class defines all properts related to delivery rules. The property values are set upon initialization.

**Sample code to implement the Basket class. You create a product Item and add the Object by invoking the add Product method of the Basket class.

<pre>
//Class Implementation

$p = new ProductItem('R01','RED WIDGET',32.95);
$p1 = new ProductItem('G01','GREEN WIDGET',24.95);
$p2 = new ProductItem('B01','BLUE WIDGET',7.95);

$product_catalogue = array($p, $p1,$p2);

$d = new DeliveryCostBand(50,4.95);
$d1 = new DeliveryCostBand(90,2.95);

$delivery_rules = array($d, $d1);


//special offers, zero delivery rate, delivery_rule, product_catalogue
$b = new Basket('R01,G01', 90, $delivery_rules, $product_catalogue);

$b->addProduct('R01');
$b->addProduct('R01');
$b->addProduct('B01');
$b->addProduct('G01');
$b->addProduct('R01');


echo $b->getTotalCostPrice()."<br />";

//Add product B01 as a product on special offer
$b->addSpecialOfferProduct('B01');


echo 'Items Cost is: '.$b->getTotalCostPrice()."<br />";
echo 'Delivery Cost is: '.$b->getDeliveryCost()."<br />";
echo 'Total Price + Delivery Cost is: '.$b->getTotal()."<br />";

//Remove product R01 from array on products on special offer
$b->removeSpecialOfferProduct('R01');
var_dump($b->special_offer_eligible_codes);
</pre>
