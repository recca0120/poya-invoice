<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OAT;

#[OAT\OpenApi(
    security: [['bearerAuth' => [], 'secret' => []]],
    externalDocs: new OAT\ExternalDocumentation(
        description: '範例程式下載',
        url: '../sample_code/sample_code.zip'
    ),
)]
#[OAT\Info(
    version: '1.0.0',
    title: '介接文件',
    attachables: [new OAT\Attachable()],
)]
#[OAT\License(name: 'MIT', identifier: 'MIT')]
#[OAT\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', scheme: 'bearer')]
abstract class Controller
{
    //
}
