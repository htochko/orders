<?php

namespace App\Services;

use App\Entity\Order;
use Symfony\Contracts\HttpClient\HttpClientInterface;
class RevoultApiWrapper
{
    public function __construct(
        private string $endPoint,
        private string $secretApiKey,
        private HttpClientInterface $client
    )
    {
    }

    const ORDERS_API_PATH = 'orders';

    public function placeOrder(Order $order): Order
    {
        $path = self::ORDERS_API_PATH;
        $response = $this->client->request(
            'POST',
            "{$this->endPoint}/{$path}",
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Authorization' => "Bearer {$this->secretApiKey}"
                ],
                'json' => ['amount' => $order->getAmount(), 'currency' => $order->getCurrency()],
            ],
        );

        $statusCode = $response->getStatusCode();
        if ($statusCode == 201 && true === array_key_exists('public_id', $response->toArray())) {
                $content = $response->toArray();
                return $order->setPaymentId($content['public_id'])
                      ->setStatus(Order::STATUS_PENDING);
        }

        return $order->setStatus(Order::STATUS_FAILED);
    }
}