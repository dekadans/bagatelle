<?php

namespace App\Services\Http;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;

trait ContentNegotiation
{
    /**
     * Returns the best match of a list of media types,
     * based on the client's selection using the Accept header.
     *
     * @param Request|ServerRequestInterface $request
     * @param string[] $acceptable
     * @return string
     */
    private function negotiateContentType(Request|ServerRequestInterface $request, array $acceptable): string
    {
        if ($request instanceof ServerRequestInterface) {
            $accept = $request->getHeaderLine('Accept') ?: '*/*';
        } else {
            $accept = implode(',', $request->headers->all('Accept')) ?: '*/*';
        }

        $acceptHeader = AcceptHeader::fromString($accept);
        $quality = fn($type) => $acceptHeader->get($type)?->getQuality() ?? 0;

        usort($acceptable, fn($a, $b) => $quality($b) <=> $quality($a));
        return $acceptable[0];
    }
}
