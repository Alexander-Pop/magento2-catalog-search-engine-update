<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order\Invoice;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Tests for view invoice totals block.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class TotalsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Totals */
    private $block;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Totals::class)
            ->setTemplate('Magento_Sales::order/totals.phtml');
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/invoices_for_items.php
     *
     * @return void
     */
    public function testInvoiceTotalsBlock(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000555');
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        $this->assertNotNull($invoice->getId());
        $blockHtml = $this->block->setOrder($order)->setInvoice($invoice)->toHtml();
        $message = '"%s" for invoice wasn\'t found or not equals to %01.2f';
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//th[contains(text(), '%s')]/following-sibling::td/span[contains(text(), '%01.2f')]",
                    __('Subtotal'),
                    $invoice->getSubtotal()
                ),
                $blockHtml
            ),
            sprintf($message, __('Subtotal'), $invoice->getSubtotal())
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//th[contains(text(), '%s')]/following-sibling::td/span[contains(text(), '%01.2f')]",
                    __('Shipping & Handling'),
                    $invoice->getShippingAmount()
                ),
                $blockHtml
            ),
            sprintf($message, __('Shipping & Handling'), $invoice->getShippingAmount())
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//tr[contains(@class, 'grand_total') and contains(.//strong, '%s')]"
                    . "//span[contains(text(), '%01.2f')]",
                    __('Grand Total'),
                    $invoice->getGrandTotal()
                ),
                $blockHtml
            ),
            sprintf($message, __('Grand Total'), $invoice->getGrandTotal())
        );
    }
}
