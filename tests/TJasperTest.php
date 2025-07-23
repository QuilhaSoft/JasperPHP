<?php

namespace JasperPHP\Tests;

use PHPUnit\Framework\TestCase;
use JasperPHP\TJasper;

class TJasperTest extends TestCase
{
    private $tempJrxmlFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempJrxmlFile = __DIR__ . '/temp_dummy.jrxml';
        $jrxmlContent = '<?xml version="1.0" encoding="UTF-8"?>
<jasperReport xmlns="http://jasperreports.sourceforge.net/jasperreports" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://jasperreports.sourceforge.net/jasperreports http://jasperreports.sourceforge.net/xsd/jasperreport.xsd" name="dummy" pageWidth="595" pageHeight="842" columnWidth="555" leftMargin="20" rightMargin="20" topMargin="20" bottomMargin="20" uuid="00000000-0000-0000-0000-000000000000">
    <queryString>
        <![CDATA[]]>
    </queryString>
    <field name="id" class="java.lang.Integer"/>
    <field name="name" class="java.lang.String"/>
    <field name="quantity" class="java.lang.Integer"/>
    <detail>
        <band height="20" splitType="Stretch">
            <textField>
                <reportElement x="0" y="0" width="100" height="20" uuid="00000000-0000-0000-0000-000000000001"/>
                <textFieldExpression><![CDATA[$F{id}]]></textFieldExpression>
            </textField>
            <textField>
                <reportElement x="100" y="0" width="100" height="20" uuid="00000000-0000-0000-0000-000000000002"/>
                <textFieldExpression><![CDATA[$F{name}]]></textFieldExpression>
            </textField>
            <textField>
                <reportElement x="200" y="0" width="100" height="20" uuid="00000000-0000-0000-0000-000000000003"/>
                <textFieldExpression><![CDATA[$F{quantity}]]></textFieldExpression>
            </textField>
        </band>
    </detail>
</jasperReport>';
        file_put_contents($this->tempJrxmlFile, $jrxmlContent);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempJrxmlFile)) {
            unlink($this->tempJrxmlFile);
        }
        parent::tearDown();
    }

    public function testCanInstantiateTJasper()
    {
        // This test still relies on an existing testReport.jrxml, which might not be ideal.
        // For now, we'll keep it as is, but it's a candidate for future refactoring.
        $this->assertInstanceOf(TJasper::class, new TJasper('testReport.jrxml', ['type' => 'pdf']));
    }

    public function testCanInstantiateTJasperWithData()
    {
        $sampleData = [
            (object)['id' => 1, 'name' => 'Item A', 'quantity' => 10],
            (object)['id' => 2, 'name' => 'Item B', 'quantity' => 20],
        ];

        $jasper = new TJasper($this->tempJrxmlFile, ['type' => 'pdf'], $sampleData);

        $reflection = new \ReflectionClass($jasper);
        $reportProperty = $reflection->getProperty('report');
        $reportProperty->setAccessible(true);
        $report = $reportProperty->getValue($jasper);

        $reportReflection = new \ReflectionClass($report);
        $dbDataProperty = $reportReflection->getProperty('dbData');
        $dbDataProperty->setAccessible(true);
        $dbData = $dbDataProperty->getValue($report);

        $this->assertEquals($sampleData, $dbData);
    }
}