<?php

namespace Example\Services;

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
                ManifestService::getLinkToManifestFile(
                    $_SESSION["api_type"]
                )
            );
        }

        $groups = $_SESSION['API_TEXT']['Groups'];
        $result = [];
        $CodeExampleNumber = (int) filter_var($eg, FILTER_SANITIZE_NUMBER_INT);

        foreach ($groups as $group) {
            foreach ($group["Examples"] as $example) {
                if ($example["ExampleNumber"] === $CodeExampleNumber) {
                    $result = $example;
                    break;
                }
            }
        }

        return $result;
    }

    public static function getCommonTexts(): array
    {
        if ($_SESSION['API_TEXT'] == null) {
            $_SESSION['API_TEXT'] = ManifestService::loadManifestData(
                ManifestService::getLinkToManifestFile(
                $_SESSION["api_type"]
                )
            );
        }

        $commonText = $_SESSION['API_TEXT']['SupportingTexts'];
        
        return $commonText;
    }

    public static function getLinkToManifestFile(string $apiType): string
    {
        switch ($apiType) :
            case "Admin":
                $manifestFile = $GLOBALS['DS_CONFIG']['AdminManifest'];
                break;
            case "Click":
                $manifestFile = $GLOBALS['DS_CONFIG']['ClickManifest'];
                break;
            case "Monitor":
                $manifestFile= $GLOBALS['DS_CONFIG']['MonitorManifest'];
                break;
            case "Rooms":
                $manifestFile = $GLOBALS['DS_CONFIG']['RoomsManifest'];
                break;
            default:
                $manifestFile = $GLOBALS['DS_CONFIG']['ESignatureManifest'];
                break;
        endswitch;
        
        return $manifestFile;
    }
}
