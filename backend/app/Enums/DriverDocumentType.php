<?php

namespace App\Enums;

use OpenApi\Attributes as OAT;


/**
 * @OA\Schema(
 *   schema="DriverDocumentType",
 *   type="enum",
 *   description="The unique identifier of a product in our catalog"
 * )
 */
enum DriverDocumentType
{
    case image_licence_front;
    case image_licence_back;
    case image_pasport_front;
    case image_pasport_address;
    case image_fase_and_pasport;
}
