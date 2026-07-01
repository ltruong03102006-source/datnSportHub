@include('owner.packages.partials.form', [
    'title' => 'Thêm gói đặt sân',
    'action' => route('owner.web.venues.packages.store', $venue),
    'method' => 'POST',
    'venue' => $venue,
    'package' => null,
])
