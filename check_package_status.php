<?php
require __DIR__ . '/vendor/autoload.php';
use App\Models\Venue;

$venues = Venue::with(['packages' => function ($query) {
    $query->where('status', 'active');
}])->where('allow_package_booking', 1)->get();

foreach ($venues as $venue) {
    echo 'Venue ' . $venue->id . ' ' . $venue->name . ' allow=' . ($venue->allow_package_booking ? '1' : '0') . ' active_packages=' . count($venue->packages) . "\n";
}
