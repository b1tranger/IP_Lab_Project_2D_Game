<?php
class Shape {
    public function area() {
        return 0;
    }
}

class Circle extends Shape {
    private $radius;
    public function __construct($r) {
        $this->radius = $r;
    }
    public function area() {
        return 3.14 * $this->radius * $this->radius;
    }
}

class Rectangle extends Shape {
    private $length;
    private $width;
    public function __construct($l, $w) {
        $this->length = $l;
        $this->width = $w;
    }
    public function area() {
        return $this->length * $this->width;
    }
}

// Demonstrate polymorphism
$shapes = [new Circle(3), new Rectangle(4, 5)];
foreach ($shapes as $shape) {
    echo "Area: " . $shape->area() . "<br>";
}
?>
