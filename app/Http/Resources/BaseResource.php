<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Override;

abstract class BaseResource extends JsonResource
{
    public static $wrap = null;

    protected string $customMessage = 'Operation successful.';

    protected int $customStatus = 200;

    public function message(string $message)
    {
        $this->customMessage = $message;
        return $this;
    }

    public function status(int $status)
    {
        $this->customStatus = $status;
        return $this;
    }

    public function withResponse($request, $response): void
    {
        $response->setStatusCode($this->customStatus);
    }
    abstract protected function data(Request $request): mixed;

    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => $this->customMessage,
            'data' => $this->data($request),
        ];
    }

    public static function collection($resource): BaseResourceCollection
    {
        return new BaseResourceCollection($resource, static::class);
    }
}

class BaseResourceCollection extends ResourceCollection
{
    public static $wrap = null;

    public $collects;

    protected string $customMessage = 'Operation successful.';
    protected int $customStatus = 200;

    public function __construct($resource, $collects)
    {
        $this->collects = $collects;

        parent::__construct($resource);
    }
    public function message(string $message): static
    {
        $this->customMessage = $message;
        return $this;
    }
    public function status(int $status): static
    {
        $this->customStatus = $status;
        return $this;
    }
    public function withResponse($request, $response): void
    {
        $response->setStatusCode($this->customStatus);
    }
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => $this->customMessage,
            'data'    => $this->collection->map(function ($item) use ($request) {
                return method_exists($item, 'data')
                    ? $item->data($request)
                    : $item->toArray($request);
            })->all(),
        ];
    }
}
