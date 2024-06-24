<?php

namespace NavOnlineInvoice;

interface RequestIdGeneratorInterface
{
    public function generate(): string;
}
