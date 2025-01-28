<?php

declare(strict_types=1);

namespace CodebarAg\LaravelInstagram\Requests;

use CodebarAg\LaravelInstagram\Actions\InstagramHandler;
use CodebarAg\LaravelInstagram\Responses\CreateMediaCollectionFromResponse;
use Illuminate\Support\Collection;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AcceptsJson;

class GetInstagramMedia extends Request
{
    use AcceptsJson;

    protected Method $method = Method::GET;

    public function __construct(
        protected bool $withChildren = true,
        protected mixed $user_id = null,
    ) {}

    /**
     * @throws \Exception
     */
    public function resolveEndpoint(): string
    {
        $user_id = $this->user_id;

        if (empty($user_id)) {
            $user_id = InstagramHandler::user()->user_id;
        }

        return $user_id.'/media';
    }

    public function defaultQuery(): array
    {
        $fields = collect([
            'id',
            'caption',
            'media_type',
            'media_url',
            'permalink',
            'thumbnail_url',
            'timestamp',
            'username',
        ]);

        $childFields = collect([
            'id',
            'media_type',
            'media_url',
            'permalink',
            'timestamp',
            'username',
        ]);

        if ($this->withChildren) {
            $fields->add('children{'.$childFields->join(',').'}');
        }

        return [
            'fields' => $fields->join(','),
        ];
    }

    public function createDtoFromResponse(Response $response): Collection
    {
        return CreateMediaCollectionFromResponse::fromResponse($response);
    }
}
