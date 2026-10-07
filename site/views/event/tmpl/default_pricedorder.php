<?php
/**
 * @package    JEM
 * @copyright  (C) 2013-2026 joomlaeventmanager.net
 * @license    https://www.gnu.org/licenses/gpl-3.0 GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$selected = array();
if (is_array($this->pricingQuote) && !empty($this->pricingQuote['lines'])) {
    foreach ((array) $this->pricingQuote['lines'] as $line) {
        $selected[(int) $line['event_price_id']] = (int) $line['quantity'];
    }
}

$currency = strtoupper((string) ($this->item->currency ?? 'EUR'));
?>
<div class="jem-pricing-order" data-jem-pricing-order data-pricing-revision="<?php echo (int) $this->item->pricing_revision; ?>">
    <p><?php echo Text::_('COM_JEM_PRICING_ORDER_HELP'); ?></p>

    <?php if ($this->pricingQuoteError !== '') : ?>
        <div class="alert alert-danger" data-jem-pricing-error>
            <?php echo $this->escape($this->pricingQuoteError); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($this->pricingOptions)) : ?>
        <div class="alert alert-info" data-jem-pricing-no-options>
            <?php echo Text::_('COM_JEM_PRICING_NO_AVAILABLE_OPTIONS'); ?>
        </div>
    <?php else : ?>
    <form method="post"
          action="<?php echo Route::_('index.php?option=com_jem&view=event&id=' . (int) $this->item->id); ?>"
          class="jem-pricing-order-form">
        <div class="table-responsive">
            <table class="table table-striped jem-pricing-options">
                <thead>
                    <tr>
                        <th><?php echo Text::_('COM_JEM_PRICING_OPTION'); ?></th>
                        <th><?php echo Text::_('COM_JEM_PRICING_UNIT_PRICE'); ?></th>
                        <th><?php echo Text::_('COM_JEM_PRICING_TAX'); ?></th>
                        <th><?php echo Text::_('COM_JEM_PRICING_QUANTITY'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ((array) $this->pricingOptions as $option) : ?>
                    <?php
                    $optionId = (int) $option->id;
                    $value = $selected[$optionId] ?? 0;
                    $max = $option->max_quantity !== null && $option->max_quantity !== ''
                        ? max(1, (int) $option->max_quantity)
                        : max(1, (int) ($this->item->maxbookeduser ?? 1));
                    ?>
                    <tr data-jem-price-option="<?php echo $optionId; ?>">
                        <td>
                            <strong><?php echo $this->escape((string) $option->name); ?></strong>
                            <?php if (trim((string) $option->description) !== '') : ?>
                                <div class="small text-muted"><?php echo $this->escape((string) $option->description); ?></div>
                            <?php endif; ?>
                            <?php if ($option->min_age !== null || $option->max_age !== null) : ?>
                                <div class="small text-muted">
                                    <?php
                                    $age = array();
                                    if ($option->min_age !== null) {
                                        $age[] = Text::sprintf('COM_JEM_PRICING_MIN_AGE', (int) $option->min_age);
                                    }
                                    if ($option->max_age !== null) {
                                        $age[] = Text::sprintf('COM_JEM_PRICING_MAX_AGE', (int) $option->max_age);
                                    }
                                    echo implode(' · ', $age);
                                    ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($this->showPricingAvailability
                                && isset($option->remaining_availability)
                                && $option->remaining_availability !== null) : ?>
                                <div class="small text-muted"
                                     data-jem-price-availability
                                     data-remaining="<?php echo (int) $option->remaining_availability; ?>">
                                    <?php echo Text::sprintf(
                                        'COM_JEM_PRICING_REMAINING_AVAILABILITY',
                                        (int) $option->remaining_availability
                                    ); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $this->escape($currency . ' ' . number_format((float) $option->amount, 2, '.', '')); ?>
                        </td>
                        <td>
                            <?php echo $this->escape((string) ($option->tax_name ?: $option->tax_type)); ?>
                            <?php if ($option->tax_rate !== null && $option->tax_rate !== '') : ?>
                                (<?php echo $this->escape(number_format((float) $option->tax_rate, 2, '.', '')); ?>%)
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="number"
                                   class="form-control form-control-sm"
                                   name="price_quantity[<?php echo $optionId; ?>]"
                                   value="<?php echo $value; ?>"
                                   min="0"
                                   max="<?php echo $max; ?>"
                                   step="1"
                                   inputmode="numeric"
                                   aria-label="<?php echo $this->escape((string) $option->name); ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <input type="hidden" name="rdid" value="<?php echo (int) $this->item->id; ?>">
        <input type="hidden" name="task" value="event.pricingquote">
        <?php echo HTMLHelper::_('form.token'); ?>

        <button type="submit" class="btn btn-primary" data-jem-pricing-calculate>
            <?php echo Text::_('COM_JEM_PRICING_CALCULATE'); ?>
        </button>
    </form>
    <?php endif; ?>

    <?php if (!empty($this->pricingOptions) && is_array($this->pricingQuote)) : ?>
        <?php
        $taxGroups = array();
        foreach ((array) ($this->pricingQuote['lines'] ?? array()) as $line) {
            $rate = number_format((float) ($line['tax_rate'] ?? 0), 2, '.', '');
            $taxMinor = (int) round(((float) ($line['line_tax'] ?? 0)) * 100);
            $taxGroups[$rate] = ($taxGroups[$rate] ?? 0) + $taxMinor;
        }
        $managementFee = is_array($this->pricingQuote['management_fee'] ?? null)
            ? $this->pricingQuote['management_fee']
            : null;
        if ($managementFee) {
            $feeRate = number_format((float) ($managementFee['tax_rate'] ?? 0), 2, '.', '');
            $feeTaxMinor = (int) round(((float) ($managementFee['line_tax'] ?? 0)) * 100);
            $taxGroups[$feeRate] = ($taxGroups[$feeRate] ?? 0) + $feeTaxMinor;
        }
        krsort($taxGroups, SORT_NUMERIC);
        ?>
        <div class="jem-pricing-quote mt-3" data-jem-pricing-quote>
            <h3><?php echo Text::_('COM_JEM_PRICING_SUMMARY'); ?></h3>
            <dl class="row">
                <dt class="col-sm-6"><?php echo Text::_('COM_JEM_PRICING_TOTAL_PLACES'); ?></dt>
                <dd class="col-sm-6" data-jem-quote-quantity><?php echo (int) $this->pricingQuote['quantity']; ?></dd>
                <dt class="col-sm-6">
                    <?php echo Text::_($managementFee
                        ? 'COM_JEM_PRICING_SUBTOTAL_NET_TOTAL'
                        : 'COM_JEM_PRICING_SUBTOTAL_NET'); ?>
                </dt>
                <dd class="col-sm-6" data-jem-quote-subtotal><?php echo $this->escape($currency . ' ' . $this->pricingQuote['subtotal_net']); ?></dd>
                <dt class="col-sm-6">
                    <?php echo Text::_($managementFee
                        ? 'COM_JEM_PRICING_TAX_TOTAL_WITH_FEES'
                        : 'COM_JEM_PRICING_TAX_TOTAL'); ?>
                </dt>
                <dd class="col-sm-6" data-jem-quote-tax><?php echo $this->escape($currency . ' ' . $this->pricingQuote['tax_total']); ?></dd>
                <?php if ($managementFee) : ?>
                    <dt class="col-sm-6"><?php echo Text::_('COM_JEM_PRICING_MANAGEMENT_FEE_NET'); ?></dt>
                    <dd class="col-sm-6" data-jem-quote-management-fee-net>
                        <?php echo $this->escape($currency . ' ' . (string) $managementFee['line_net']); ?>
                    </dd>
                    <dt class="col-sm-6"><?php echo Text::_('COM_JEM_PRICING_MANAGEMENT_FEE_TAX'); ?></dt>
                    <dd class="col-sm-6" data-jem-quote-management-fee-tax>
                        <?php echo $this->escape($currency . ' ' . (string) $managementFee['line_tax']); ?>
                    </dd>
                    <dt class="col-sm-6"><?php echo Text::_('COM_JEM_PRICING_MANAGEMENT_FEE_GROSS'); ?></dt>
                    <dd class="col-sm-6" data-jem-quote-management-fee-gross>
                        <?php echo $this->escape($currency . ' ' . (string) $managementFee['line_gross']); ?>
                    </dd>
                <?php endif; ?>
                <?php foreach ($taxGroups as $rate => $taxMinor) : ?>
                    <dt class="col-sm-6">
                        <?php echo Text::sprintf('COM_JEM_PRICING_TAX_GROUP', $this->escape($rate)); ?>
                    </dt>
                    <dd class="col-sm-6"
                        data-jem-quote-tax-group
                        data-tax-rate="<?php echo $this->escape($rate); ?>">
                        <?php echo $this->escape(
                            $currency . ' ' . number_format($taxMinor / 100, 2, '.', '')
                        ); ?>
                    </dd>
                <?php endforeach; ?>
                <dt class="col-sm-6"><?php echo Text::_('COM_JEM_PRICING_GRAND_TOTAL'); ?></dt>
                <dd class="col-sm-6" data-jem-quote-total><strong><?php echo $this->escape($currency . ' ' . $this->pricingQuote['grand_total']); ?></strong></dd>
            </dl>

            <div class="jem-pricing-quote-lines">
            <?php foreach ((array) $this->pricingQuote['lines'] as $line) : ?>
                <div class="small" data-jem-quote-line="<?php echo (int) $line['event_price_id']; ?>">
                    <?php
                    echo $this->escape(
                        (string) $line['name']
                        . ' × ' . (int) $line['quantity']
                        . ' — ' . $currency . ' ' . (string) $line['line_gross']
                        . ' (' . (string) $line['tax_rate'] . '%)'
                    );
                    ?>
                </div>
            <?php endforeach; ?>
            </div>

            <?php
            $quoteFingerprint = (string) ($this->pricingQuote['quote_fingerprint'] ?? '');
            $operationReference = (string) ($this->pricingQuote['operation_reference'] ?? '');
            ?>
            <?php if (
                preg_match('/^[a-f0-9]{64}$/D', $quoteFingerprint) === 1
                && JemRegistrationIdentity::isOperationReference($operationReference)
            ) : ?>
                <form method="post"
                      action="<?php echo Route::_('index.php?option=com_jem', false); ?>"
                      class="jem-pricing-confirm mt-3"
                      data-jem-pricing-confirm>
                    <input type="hidden" name="task" value="event.pricingconfirm">
                    <input type="hidden" name="rdid" value="<?php echo (int) $this->item->id; ?>">
                    <input type="hidden"
                           name="quote_fingerprint"
                           value="<?php echo $this->escape($quoteFingerprint); ?>">
                    <input type="hidden"
                           name="operation_reference"
                           value="<?php echo $this->escape($operationReference); ?>">

                    <?php foreach ((array) ($this->pricingQuote['lines'] ?? array()) as $confirmLine) : ?>
                        <input type="hidden"
                               name="price_quantity[<?php echo (int) $confirmLine['event_price_id']; ?>]"
                               value="<?php echo (int) $confirmLine['quantity']; ?>">
                    <?php endforeach; ?>

                    <?php echo HTMLHelper::_('form.token'); ?>

                    <button type="submit"
                            class="btn btn-success"
                            data-jem-pricing-confirm-submit>
                        <?php echo Text::_('COM_JEM_PRICING_CONFIRM'); ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
