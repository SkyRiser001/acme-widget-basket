<?php

declare(strict_types=1);

namespace Acme\Tests;

use Acme\AcmeWidgetCo;
use Acme\Http\Kernel;
use PHPUnit\Framework\TestCase;

final class KernelTest extends TestCase
{
    public function testTheCatalogueAdvertisesProductsOffersAndBands(): void
    {
        $response = $this->kernel()->handle('GET', '/api/catalogue', '');

        self::assertSame(200, $response->status);
        self::assertIsArray($response->data);
        self::assertSame(['R01', 'G01', 'B01'], array_column($response->data['products'], 'code'));
        self::assertSame('$32.95', $response->data['products'][0]['price']);
        self::assertSame(
            ['Buy one red widget, get the second half price'],
            $response->data['offers'],
        );
        self::assertSame(5000, $response->data['deliveryTiers'][0]['belowCents']);
        self::assertSame(495, $response->data['deliveryTiers'][0]['chargeCents']);
        self::assertNull($response->data['deliveryTiers'][2]['belowCents']);
        self::assertSame(0, $response->data['deliveryTiers'][2]['chargeCents']);
    }

    public function testAQuoteUsesTheSameTotalsAsTheBasket(): void
    {
        $response = $this->kernel()->handle('POST', '/api/basket', '{"items":["R01","R01"]}');

        self::assertSame(200, $response->status);
        self::assertIsArray($response->data);
        self::assertSame('$65.90', $response->data['subtotal']);
        self::assertSame('$16.48', $response->data['discounts'][0]['amount']);
        self::assertSame(4942, $response->data['goodsTotalCents']);
        self::assertSame('$4.95', $response->data['delivery']);
        self::assertSame('$54.37', $response->data['total']);
    }

    public function testTheFiveItemSampleIsPricedOverHttp(): void
    {
        $response = $this->kernel()->handle(
            'POST',
            '/api/basket?source=counter',
            '{"items":["B01","B01","R01","R01","R01"]}',
        );

        self::assertSame(200, $response->status);
        self::assertIsArray($response->data);
        self::assertSame('$98.27', $response->data['total']);
        self::assertSame(0, $response->data['deliveryCents']);
    }

    public function testAnUnknownCodeIsAClientError(): void
    {
        $response = $this->kernel()->handle('POST', '/api/basket', '{"items":["nope"]}');

        self::assertSame(422, $response->status);
        self::assertSame(['error' => 'Unknown product code: NOPE'], $response->data);
    }

    public function testMalformedJsonIsRejected(): void
    {
        $response = $this->kernel()->handle('POST', '/api/basket', '{');

        self::assertSame(400, $response->status);
    }

    public function testAnUnknownRouteIsNotFound(): void
    {
        $response = $this->kernel()->handle('GET', '/api/missing', '');

        self::assertSame(404, $response->status);
    }

    public function testAPreflightDoesNotPriceABasket(): void
    {
        $response = $this->kernel()->handle('OPTIONS', '/api/basket', '');

        self::assertSame(204, $response->status);
        self::assertNull($response->data);
    }

    private function kernel(): Kernel
    {
        return new Kernel((new AcmeWidgetCo())->configure());
    }
}
