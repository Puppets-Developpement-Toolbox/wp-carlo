/**
 * nice date picker
 * Created by ollie on 2017/4/27.
 */
(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
        typeof define === 'function' && define.amd ? define(factory) :
            (global.niceDatePicker = factory());
}(this, function () {
    'use strict';

    var niceDatePicker = function ($params) {
        this.$warpper = null;
        this.monthData = null;
        this.$params = $params;
        this.init(this.$params);
    };

    niceDatePicker.prototype.getMonthData = function (year, month) {
        var year, month;
        var ret = [];

        if (!year || !month) {

            var today = new Date();

            year = today.getFullYear();

            month = today.getMonth() + 1;
        }
        var firstDay = new Date(year, month - 1, 1);//当月的第一天

        var firstDayWeekDay = firstDay.getDay();//当月第一天是周几

        if (firstDayWeekDay === 0) {

            firstDayWeekDay = 7;
        }

        year = firstDay.getFullYear();

        month = firstDay.getMonth() + 1;
        
        var lastDayOfLastMonth = new Date(year, month - 1, 0);//上个月的最后一天

        var lastDateOfLastMonth = lastDayOfLastMonth.getDate();//上个月最后一天是几号

        var preMonthDayCount = firstDayWeekDay - 1;//需要显示上个月几个日期

        var lastDay = new Date(year, month, 0);//当月的最后一天

        var lastDate = lastDay.getDate()//当月最后天是几号
        
        var today = new Date();
            
        var styleCls = '';
        for (var i = 0; i < 7 * 6; i++) {

            var date = i + 1 - preMonthDayCount;

            var showDate = date;

            var thisMonth = month;
            
            if (date <= 0) {
                //上个月
                thisMonth = month - 1;
                showDate = lastDateOfLastMonth + date;
                styleCls = 'nice-gray';

            } else if (date > lastDate) { 
                thisMonth = month + 1;
                showDate = showDate - lastDate;
                styleCls = 'nice-gray';
            } else {
                
                if (showDate === today.getDate() && thisMonth === today.getMonth() + 1) {
                    styleCls = 'nice-current';
                } 
                else if (showDate < today.getDate() && thisMonth === today.getMonth() + 1){
                  styleCls = 'nice-past';
                }
                else if ( thisMonth < today.getMonth() + 1 && year-1900 <= today.getYear()){
                  styleCls = 'nice-past';
                }
                else {
                    styleCls = 'nice-normal';
                }


            }

            if (thisMonth === 13) {
                thisMonth = 1;
            }
            if (thisMonth === 0) {
                thisMonth = 12;
            }

            ret.push({
                month: thisMonth,
                date: date,
                showDate: showDate,
                styleCls: styleCls
            });
        }
        return {
            year: year,
            month: month,
            date: ret
        };
    };
    
    niceDatePicker.prototype.buildUi = function (year, month) {
        this.monthData = this.getMonthData(year, month);
        this.dayWords = [Drupal.t('L'), Drupal.t('M'), Drupal.t('M'), Drupal.t('J'), Drupal.t('V'), Drupal.t('S'), Drupal.t('D')];
        this.monthsWords = [Drupal.t('Janvier'), Drupal.t('Février'), Drupal.t('Mars'), Drupal.t('Avril'), Drupal.t('Mai'), Drupal.t('Juin'), Drupal.t('Juillet'), Drupal.t('Aout'), Drupal.t('Septembre'), Drupal.t('Octobre'), Drupal.t('Novembre'), Drupal.t('Décembre'),];


        var html = '<div class="nice-date-picker-wrapper">' +
            '<div class="nice-date-picker-header">' +
            '<a href="#" class="prev-date-btn">&lt;</a>';
        
        html += '<span class="nice-date-title">' + this.monthsWords[this.monthData.month - 1] + ' ' + this.monthData.year + '</span>';
        /*
        if (!this.$params.mode) {
            this.$params.mode = 'fr';
            html += '<span class="nice-date-title">' + this.monthData.year + '年 - ' + this.monthData.month + '月</span>';
        } else if (this.$params.mode === 'en') {
            html += '<span class="nice-date-title">' + this.enMonthsWords[this.monthData.month - 1] + ',' + this.monthData.year + '</span>';
        } else if (this.$params.mode === 'fr') {
            html += '<span class="nice-date-title">' + this.monthData.year + '年 - ' + this.monthData.month + '月</span>';
        }
        */
        html += '<a href="#" class="next-date-btn">&gt;</a>' +
            '</div>' +
            '<div class="nice-date-picker-body">' +
            '<table>' +
            '<thead>' +
            '<tr>';
        for (var i = 0; i < this.dayWords.length; i++) {
            html += '<th>' + this.dayWords[i] + '</th>';
        }
        /*if (!this.$params.mode) {
            this.$params.mode = 'fr';
            for (var i = 0; i < this.dayWords[0].length; i++) {
                html += '<th>' + this.dayWords[0][i] + '</th>';
            }
        } else if (this.$params.mode === 'en') {
            for (var i = 0; i < this.dayWords[1].length; i++) {
                html += '<th>' + this.dayWords[1][i] + '</th>';
            }
        } else if (this.$params.mode === 'fr') {
            for (var i = 0; i < this.dayWords[0].length; i++) {
                html += '<th>' + this.dayWords[0][i] + '</th>';
            }
        }*/
        html += '</tr>' +
            '</thead>' +
            '<tbody>';
        
        for (var i = 0; i < this.monthData.date.length; i++) {
            if (i % 7 === 0) {
                html += '<tr>';
            }
            var zemonth = this.monthData.month;
            zemonth = (zemonth < 10) ? '0'+ zemonth : zemonth; // 2 digits for month
            var zeday = this.monthData.date[i].showDate; // 2 digits for day
            zeday = (zeday < 10) ? '0'+ zeday : zeday;
            var dataDate = this.monthData.year + '-' + zemonth + '-' + zeday;
            var selectedClass = (dataDate === this.$params.inputDateVal && this.monthData.date[i].styleCls === 'nice-normal') ? ' nice-selected' : '';
            //this.$params.inputDateVal
            html += '<td class="' + this.monthData.date[i].styleCls + selectedClass + '" data-date="' + dataDate + '"><span>' + this.monthData.date[i].showDate + '</span></td>';
            if (i % 7 === 6) {
                html += '</tr>';
            }
        }

        html += '</tbody>' +
            '</table>' +
            '</div>' +
            '</div>';


        return html;

    };

    niceDatePicker.prototype.render = function (direction, $params) {
        var year, month;
        if (this.monthData) {

            year = this.monthData.year;
            month = this.monthData.month;

        } else {
            year = $params.year;
            month = $params.month;
        }
        if (direction === 'prev') {
            month--;
            if (month === 0) {
                month = 12;
                year--;
            }
        }
        if (direction === 'next') {
            month++;

        }
        var html = this.buildUi(year, month);
        this.$warpper.innerHTML = html;
    };
    niceDatePicker.prototype.init = function ($params) {
        this.$warpper = $params.dom;
        this.render('', $params);
        var _this = this;
        this.$warpper.addEventListener('click', function (e) {
            var $target = e.target;
            if ($target.classList.contains('prev-date-btn')) {

                _this.render('prev');
                e.preventDefault();

            }
            if ($target.classList.contains('next-date-btn')) {

                _this.render('next');
                e.preventDefault();

            }
            if (($target.classList.contains('nice-normal'))) {
              if ($target.classList.contains('nice-selected')) {
                $target.classList.remove('nice-selected');
                $params.onClickDate(false);
              }
              else{
                $params.onClickDate($target.getAttribute('data-date'));
                if($params.dom.querySelector('.nice-selected')){
                  $params.dom.querySelector('.nice-selected').classList.remove('nice-selected');
                }
                $target.classList.add('nice-selected');
                e.preventDefault();
              }
            }
        }, false);
        this.$warpper.addEventListener('mouseover', function (e) {
            var $target = e.target;
            if ($target.classList.contains('nice-normal')) {

                $target.classList.add('nice-active');
            }
        }, false);
        this.$warpper.addEventListener('mouseout', function (e) {
            var $target = e.target;
            if ($target.classList.contains('nice-normal')) {

                $target.classList.remove('nice-active');
            }

        }, false);

    };
    return niceDatePicker;
}));