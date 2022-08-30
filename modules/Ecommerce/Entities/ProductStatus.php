<?php

namespace Modules\Ecommerce\Entities;

enum ProductStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
}
