<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Enums;

enum CardBrand: string
{
    case Visa = 'visa';
    case Mastercard = 'mastercard';
    case Amex = 'amex';
    case Discover = 'discover';
    case JCB = 'jcb';
    case Diners = 'diners';
    case UnionPay = 'unionpay';
}
