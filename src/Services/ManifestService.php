<?php

namespace DocuSign\Services;

class ManifestService
{
    public static function loadManifestData(string $apiFileLink): array
    {
        return json_decode(file_get_contents($apiFileLink), true);
    }

    public static function replacePlaceholders(string $search, string $replacer, string $textWithPlaceholders): string
    {
        return str_replace(
            $search,
            $replacer,
            $textWithPlaceholders
        );
    }

    public static function getPageText(string $eg): array
    {
        if ($_SESSION['API_TEXT'] == null) {
            $_SESSION['API_TEXT'] = ManifestService::loadManifestData(
                $GLOBALS['DS_CONFIG']['CodeExamplesManifest']
            );
        }

        $apiType = ManifestService::getAPIByLink(preg_replace('/[0-9]+/', '', $eg));
        $apis = $_SESSION['API_TEXT']['APIs'];
        $result = [];
        $CodeExampleNumber = (int) filter_var($eg, FILTER_SANITIZE_NUMBER_INT);

        foreach ($apis as $api) {
            if ($api["Name"] === $apiType) {
                $groups = $api["Groups"];

                foreach ($groups as $group) {
                    foreach ($group["Examples"] as $example) {
                        if ($example["ExampleNumber"] === $CodeExampleNumber) {
                            $result = $example;
                            break;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public static function getCommonTexts(): array
    {
        if ($_SESSION['API_TEXT'] == null) {
            $_SESSION['API_TEXT'] = ManifestService::loadManifestData(
                $GLOBALS['DS_CONFIG']['CodeExamplesManifest']
            );
        }

        $commonText = $_SESSION['API_TEXT']['SupportingTexts'];
        
        return $commonText;
    }

    public static function getAPIByLink(string $link): string
    {
        $link = preg_replace('/\d/', '', $link);

        switch ($link) :
            case "aeg":
                $currentAPI = ApiTypes::ADMIN;
                break;
            case "ceg":
                $currentAPI = ApiTypes::CLICK;
                break;
            case "meg":
                $currentAPI= ApiTypes::MONITOR;
                break;
            case "reg":
                $currentAPI = ApiTypes::ROOMS;
                break;
            case "con":
                $currentAPI = ApiTypes::CONNECT;
                break;
            case "mae":
                $currentAPI = ApiTypes::MAESTRO;
                break;
            case "web":
                $currentAPI = ApiTypes::WEBFORMS;
                break;
            default:
                $currentAPI = ApiTypes::ESIGNATURE;
                break;
        endswitch;
        
        return $currentAPI;
    }
}
