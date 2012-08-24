<?php
/**
 * ZOOLU - Content Management System
 * Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 *
 * LICENSE
 *
 * This file is part of ZOOLU.
 *
 * ZOOLU is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ZOOLU is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ZOOLU. If not, see http://www.gnu.org/licenses/gpl-3.0.html.
 *
 * For further information visit our website www.getzoolu.org
 * or contact us at zoolu@getzoolu.org
 *
 * @category   ZOOLU
 * @package    application.zoolu.modules.global.views.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * StatisticHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-08-12: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

class StatisticHelper
{

    private $BAR_COLORS = array('0' => array('R' => 117, 'G' => 68, 'B' => 128, 'Alpha' => 100));

    /**
     * @var Core
     */
    private $core;

    /**
     * Constructor
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * getNewsletterInfo
     * @param MailChimpCampaign $objNewsletter
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getNewsletterInfo(MailChimpCampaign $objCampaign, $strDeliveryDate, $strFilterTitle)
    {
        $strOutput = '';

        $strOutput .= '
    <div class="box-12">
      <div class="editbox">
        <div class="cornertl">
          <div></div>
        </div>
        <div class="cornertr"></div>
        <div class="editboxtitlecontainer">
          <div class="editboxtitle">' . $this->core->translate->_('General_information') . '</div><div class="clear"></div>
        </div>
        <div style="" class="editboxfields">
          <div class="field-6">
            <div class="field">
              <label class="fieldtitle">
                ' . $this->core->translate->_('Subject') . '
              </label>
              <br>
              ' . $objCampaign->getTitle() . '
            </div>
          </div>
          <div class="field-6">
            <div class="field">
              <label class="fieldtitle">
                ' . $this->core->translate->_('Recipients') . '
              </label>
              <br>
              ' . $objCampaign->getRecipientCount() . '
            </div>
          </div>
          <div class="field-6">
            <div class="field">
              <label class="fieldtitle">
                ' . $this->core->translate->_('Delivery_date') . '
              </label>
              <br>
              ' . $strDeliveryDate . '
            </div>
          </div>
          <div class="field-6">
            <div class="field">
              <label class="fieldtitle">
                ' . $this->core->translate->_('Sent_to_filter') . '
              </label>
              <br>
              ' . $strFilterTitle . '
            </div>
          </div>
          <div class="clear"></div>
        </div>
        <div class="cornerbl"></div>
        <div class="cornerbr"></div>
      </div>
    </div>';

        return $strOutput;
    }

    public function getNewsletterStatistics(MailChimpCampaign $objCampaign)
    {
        $intRecipients = $objCampaign->getRecipientCount();
        $intDelivered = $objCampaign->getSuccessfulDelivers();
        $strOutput = '';
        $intTimestamp = time();

        $this->drawChart($this->getPercentage($intRecipients, $objCampaign->getUniqueOpenCount()), 'OpenChart_' . $objCampaign->getCampaignId());
        $this->drawChart($this->getPercentage($intRecipients, $intRecipients - $objCampaign->getUniqueOpenCount()), 'UnopenChart_' . $objCampaign->getCampaignId());
        $this->drawChart($this->getPercentage($intRecipients, $objCampaign->getUniqueRecipientClickCount()), 'ClickChart_' . $objCampaign->getCampaignId());

        //Transform Data
        $arrClickStatistics = $objCampaign->getClickStatistics();
        $strClickStatistics = (count($arrClickStatistics) > 0) ? json_encode((object) $arrClickStatistics) : '';

        $arrUnsubscribes = $objCampaign->getUnsubscribes();
        $strUnsubscribes = (count($arrUnsubscribes) > 0) ? json_encode((object) $arrUnsubscribes) : '';

        $arrComplaints = $objCampaign->getComplaints();
        $strComplaints = (count($arrComplaints) > 0) ? json_encode((object) $arrComplaints) : '';

        $arrBounces = $objCampaign->getBounces();
        $cntBounces = count($arrBounces);
        for ($i = 0; $i < $cntBounces; $i++) {
            unset($arrBounces[$i]['message']);
        }
        $strBounces = (count($arrBounces) > 0) ? json_encode((object) $arrBounces) : '';

        $strOutput .= '
      <!-- JSON Data -->
      <div id="statClicks" style="display:none;">' . $strClickStatistics . '</div>
      <div id="statUnsubscribes" style="display:none;">' . $strUnsubscribes . '</div>
      <div id="statComplaints" style="display:none">' . $strComplaints . '</div>
      <div id="statBounces" style="display:none">' . $strBounces . '</div>
      <!-- end JSON data -->
      <div class="box-12">
      <div class="editbox">
        <div class="cornertl">
          <div></div>
        </div>
        <div class="cornertr"></div>
        <div class="editboxtitlecontainer">
          <div class="editboxtitle">' . $this->core->translate->_('Statistics') . '</div><div class="clear"></div>
        </div>
        <div style="" class="editboxfields">
          <div class="field-4">
            <div class="field">
              <label class="fieldtitle">
                ' . $this->core->translate->_('Open_chart') . '
              </label><br />
              <img src="/tmp/images/OpenChart_' . $objCampaign->getCampaignId() . '.png?v=' . $intTimestamp . '" />
            </div>
          </div>
          <div class="field-4">
            <div class="field">
              <label class="fieldtitle">
                ' . $this->core->translate->_('Unopen_chart') . '
              </label><br />
              <img src="/tmp/images/UnopenChart_' . $objCampaign->getCampaignId() . '.png?v=' . $intTimestamp . '" />
            </div>
          </div>
          <div class="field-4">
            <div class="field">
              <label class="fieldtitle">
                ' . $this->core->translate->_('Click_chart') . '
              </label><br />
              <img src="/tmp/images/ClickChart_' . $objCampaign->getCampaignId() . '.png?v=' . $intTimestamp . '" style="cursor:pointer;" onclick="myForm.showStatisticTable(\'clicks\', \'statClicks\', true)" />
            </div>
          </div>
          <div class="field-4">
            <div class="field">
              <label class="fieldtitle">
                <a onclick="myForm.showStatisticTable(\'unsubscribes\', \'statUnsubscribes\', false)" href="#">' . $this->core->translate->_('Unsubscribers') . '</a>
              </label>
              ' . $this->getPercentage($intRecipients, $objCampaign->getUnsubscribeCount()) . '
              <a onclick="myForm.exportStatistics(\'unsubscribes\')" href="#">(CSV)</a>
            </div>
          </div>
          <div class="field-4">
            <div class="field">
              <label class="fieldtitle">
                <a onclick="myForm.showStatisticTable(\'complaints\', \'statComplaints\', false)" href="#">' . $this->core->translate->_('Complaints') . '</a>
              </label>
              ' . $this->getPercentage($intRecipients, $objCampaign->getComplaintCount()) . '
              <a onclick="myForm.exportStatistics(\'complaints\')" href="#">(CSV)</a>
            </div>
          </div>
          <div class="field-4">
            <div class="field">
              <label class="fieldtitle">
                ' . $this->core->translate->_('Delivered_to') . '
              </label>
              ' . $this->getPercentage($intRecipients, $objCampaign->getSuccessfulDelivers()) . '
            </div>
          </div>
          <div class="field-4">
            <div class="field">
              <label class="fieldtitle">
                ' . $this->core->translate->_('Unique_opens') . '
              </label>
              ' . $this->getPercentage($objCampaign->getOpenCount(), $objCampaign->getUniqueOpenCount()) . '
            </div>
          </div>
          <div class="field-4">
            <div class="field">
              <label class="fieldtitle">
                ' . $this->core->translate->_('Opens_total') . '
              </label>
              ' . $objCampaign->getOpenCount() . '
            </div>
          </div>
          <div class="field-4">
            <div class="field">
              <label class="fieldtitle">
                <a onclick="myForm.showStatisticTable(\'bounces\', \'statBounces\', false)" href="#">' . $this->core->translate->_('Bounce') . '</a>
              </label>
              ' . $objCampaign->getBounceCount() . '
              <a onclick="myForm.exportStatistics(\'bounces\')" href="#">(CSV)</a>
            </div>
          </div>
          <div class="field-4">
            <div class="field">
              <label class="fieldtitle">
                ' . $this->core->translate->_('Total_clicks') . '
              </label>
              ' . $objCampaign->getClicksCount() . '
            </div>
          </div>
          <div class="field-4">
            <div class="field">
              <label class="fieldtitle">
                ' . $this->core->translate->_('Forward') . '
              </label>
              ' . $objCampaign->getForwardCount() . '
            </div>
          </div>
          <div class="field-4">
            <div class="field">
              <label class="fieldtitle">
                ' . $this->core->translate->_('Forward_Opens') . '
              </label>
              ' . $objCampaign->getForwardOpenCount() . '
            </div>
          </div>
          <div class="clear"></div>
        </div>
        <div class="cornerbl"></div>
        <div class="cornerbr"></div>
      </div>
    </div>';

        return $strOutput;
    }


    public function getNewsletterCountries(MailChimpCampaign $objCampaign)
    {
        $strOutput = '';

        $strOutput .= '
    <div class="box-12">
      <div class="editbox">
        <div class="cornertl">
          <div></div>
        </div>
        <div class="cornertr"></div>
        <div class="editboxtitlecontainer">
          <div class="editboxtitle">' . $this->core->translate->_('Country_statistics') . '</div><div class="clear"></div>
        </div>
        <div style="" class="editboxfields">';

        //Display all the countries
        $arrCountries = $objCampaign->getCountryStatistics();
        if (count($arrCountries) > 0) {
            foreach ($arrCountries as $arrCountry) {
                $strOutput .= '<div class="field-4">
            <div class="field">
              <label class="fieldtitle">
                ' . $arrCountry['name'] . '
              </label>
              ' . $arrCountry['opens'] . '
            </div>
          </div>';
            }
        }

        $strOutput .= '<div class="clear"></div>
        </div>
        <div class="cornerbl"></div>
        <div class="cornerbr"></div>
      </div>
    </div>';

        return $strOutput;
    }

    /**
     * drawChart
     * @param number $intCampaignValue
     */
    private function drawChart($intCampaignValue, $strName)
    {
        $objChart = new Chart(200, 300, array($intCampaignValue), array('Campaign'));
        $objChart->drawBarChart(array('DisplayValues' => true, 'OverrideColors' => $this->BAR_COLORS));
        $objChart->render(GLOBAL_ROOT_PATH . 'public/tmp/images/' . $strName . '.png');
    }

    /**
     * getPercentage
     * @return string
     */
    public function getPercentage($intTotal, $intValue, $intPrecision = 2)
    {
        if ($intTotal != 0 && $intValue != 0) {
            $intRet = $intValue / $intTotal * 100;
            $intRet = round($intRet, $intPrecision);
            return $intRet . '%';
        } else {
            return '0%';
        }
    }
}

?>