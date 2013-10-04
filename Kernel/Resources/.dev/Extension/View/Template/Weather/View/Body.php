<?php

/**
 *
 * @package   Molajo
 * @copyright 2013 Amy Stephen. All rights reserved.
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */

if ((int)$this->row->results == 0) {
    return;
};
?>

<div class="weather-wrapper">
    <div class="weather current">
        <div class="weather-icon float-left">
            <img src="<?php echo $this->row->now_icon; ?>" alt="<?php echo $this->row->now_condition; ?>"
                 title="<?php echo $this->row->now_condition; ?>"/>
        </div>
        <div class="weather-info forecast-info float-left">
            <b><?php echo $this->language->translate('Today'); ?></b><br/>

            <div class="temp"><?php echo $this->row->now_temp; ?></div>
            <div class="condition"><?php echo $this->row->now_condition; ?></div>
            <div class="wind"><?php echo $this->row->now_wind_condition; ?></div>
        </div>
    </div>
    <h5 class="weather"><?php echo $this->language->translate('Forecast'); ?></h5>

    <div class="weather forecast forecast-1">
        <div class="weather-icon float-left">
            <img src="<?php echo $this->row->day1_forecast_icon; ?>"
                 alt="<?php echo $this->row->day1_forecast_day_of_week; ?>"
                 title="<?php echo $this->row->day1_forecast_day_of_week; ?>"/>
        </div>
        <div class="weather-info forecast-info float-left">
            <b><?php echo $this->row->day1_forecast_day_of_week; ?></b><br/>
            <?php echo $this->row->day1_forecast_low_temperature; ?>
            | <?php echo $this->row->day1_forecast_high_temperature; ?><br/>
            <?php echo $this->row->day1_forecast_condition; ?>
        </div>
    </div>
    <div class="weather forecast forecast-2">
        <div class="weather-icon float-left">
            <img src="<?php echo $this->row->day2_forecast_icon; ?>"
                 alt="<?php echo $this->row->day2_forecast_day_of_week; ?>"
                 title="<?php echo $this->row->day2_forecast_day_of_week; ?>"/>
        </div>
        <div class="weather-info forecast-info float-left">
            <b><?php echo $this->row->day2_forecast_day_of_week; ?></b><br/>
            <?php echo $this->row->day2_forecast_low_temperature; ?>
            | <?php echo $this->row->day2_forecast_high_temperature; ?><br/>
            <?php echo $this->row->day2_forecast_condition; ?>
        </div>
    </div>
    <div class="weather forecast forecast-3">
        <div class="weather-icon float-left">
            <img src="<?php echo $this->row->day3_forecast_icon; ?>"
                 alt="<?php echo $this->row->day3_forecast_day_of_week; ?>"
                 title="<?php echo $this->row->day3_forecast_day_of_week; ?>"/>
        </div>
        <div class="weather-info forecast-info float-left">
            <b><?php echo $this->row->day3_forecast_day_of_week; ?></b><br/>
            <?php echo $this->row->day3_forecast_low_temperature; ?>
            | <?php echo $this->row->day3_forecast_high_temperature; ?><br/>
            <?php echo $this->row->day3_forecast_condition; ?>
        </div>
    </div>
    <div class="weather forecast forecast-4">
        <div class="weather-icon float-left">
            <img src="<?php echo $this->row->day4_forecast_icon; ?>"
                 alt="<?php echo $this->row->day4_forecast_day_of_week; ?>"
                 title="<?php echo $this->row->day4_forecast_day_of_week; ?>"/>
        </div>
        <div class="weather-info forecast-info float-left">
            <b><?php echo $this->row->day4_forecast_day_of_week; ?></b><br/>
            <?php echo $this->row->day4_forecast_low_temperature; ?>
            | <?php echo $this->row->day4_forecast_high_temperature; ?><br/>
            <?php echo $this->row->day4_forecast_condition; ?>
        </div>
    </div>
</div>
