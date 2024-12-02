<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Utils
{
    public function GetJsonResponse(Request $request, $var, $ignoredFields = []): JsonResponse
    {
        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];

        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $dateNormalizer = new DateTimeNormalizer(['datetime_format' => 'Y-m-d H:i:s']);

        $serializer = new Serializer([$dateNormalizer, $normalizer], [$encoder]);
        
        // Enlever des champs inutiles
        array_push($ignoredFields, '__initializer__', '__cloner__', '__isInitialized__'); 
        $data = $serializer->serialize($var, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => $ignoredFields]);
        
        return new JsonResponse(json_decode($data), JsonResponse::HTTP_OK);
    }

    public static function ErrorMissingArguments(): JsonResponse
    {
        return new JsonResponse(['error' => 'MISSING_ARGUMENTS_PARAMETERS'], JsonResponse::HTTP_BAD_REQUEST);
    }

    public static function ErrorMissingArgumentsDebug($content): JsonResponse
    {
        return new JsonResponse(['error' => 'MISSING_ARGUMENTS_PARAMETERS', 'content' => $content], JsonResponse::HTTP_BAD_REQUEST);
    }

    public static function ErrorCustom($message): JsonResponse
    {
        return new JsonResponse(['error' => $message], JsonResponse::HTTP_BAD_REQUEST);
    }
}
