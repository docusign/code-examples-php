<?php

namespace DocuSign\Tests;

use PHPUnit\Framework\TestCase;
use DocuSign\Services\ApiTypes;

final class CreateBrandTest extends TestCase
{
    public function testCreateBrand_CorrectInputValues_ReturnArray()
    {
        // Arrange
        $testConfig = new TestConfig();
        JWTLoginMethod::jwtAuthenticationMethod(ApiTypes::ESIGNATURE, $testConfig);

        // Act
        $brandId = DocuSignHelpers::createBrand($testConfig);
        $testConfig->setBrandId($brandId['brand_id']);

        // Assert
        $this->assertNotEmpty($brandId);
        $this->assertNotNull($brandId);
    }
}