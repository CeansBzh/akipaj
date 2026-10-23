<?php

namespace Tests\Unit;

use App\Http\Requests\Photo\StorePhotoRequest;
use PHPUnit\Framework\TestCase;

class StorePhotoRequestTest extends TestCase
{
    public function test_exif_rational_coordinates_are_converted_to_decimal_degrees(): void
    {
        $request = new StorePhotoRequest;

        // 48° 23' 25.2" N, 4° 29' 9" W (Brest)
        $this->assertEqualsWithDelta(48.390333, $request->toGps(['48/1', '23/1', '252/10'], 'N'), 0.0001);
        $this->assertEqualsWithDelta(-4.485833, $request->toGps(['4/1', '29/1', '9/1'], 'W'), 0.0001);
    }

    public function test_coordinates_can_be_given_as_a_string(): void
    {
        $this->assertEqualsWithDelta(-33.5, (new StorePhotoRequest)->toGps('33, 30, 0', 'S'), 0.0001);
    }

    public function test_malformed_parts_are_ignored(): void
    {
        $this->assertEqualsWithDelta(10.0, (new StorePhotoRequest)->toGps(['10', '0/1/2', '0'], 'E'), 0.0001);
    }
}
