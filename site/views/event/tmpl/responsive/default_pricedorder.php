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

    <?php if (is_array($this->pricingQuote)) : ?>
        <div class="jem-pricing-quote mt-3" data-jem-pricing-quote>
            <h3><?php echo Text::_('COM_JEM_PRICING_SUMMARY'); ?></h3>
            <dl class="row">
                <dt class="col-sm-6"><?php echo Text::_('COM_JEM_PRICING_TOTAL_PLACES'); ?></dt>
                <dd class="col-sm-6" data-jem-quote-quantity><?php echo (int) $this->pricingQuote['quantity']; ?></dd>
                <dt class="col-sm-6"><?php echo Text::_('COM_JEM_PRICING_SUBTOTAL_NET'); ?></dt>
                <dd class="col-sm-6" data-jem-quote-subtotal><?php echo $this->escape($currency . ' ' . $this->pricingQuote['subtotal_net']); ?></dd>
                <dt class="col-sm-6"><?php echo Text::_('COM_JEM_PRICING_TAX_TOTAL'); ?></dt>
                <dd class="col-sm-6" data-jem-quote-tax><?php echo $this->escape($currency . ' ' . $this->pricingQuote['tax_total']); ?></dd>
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
        </div>
    <?php endif; ?>
</div>
