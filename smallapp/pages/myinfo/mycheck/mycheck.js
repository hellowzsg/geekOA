const wxCharts = require('../../../utils/wxcharts.js');
const util = require("../../../utils/util.js");
const app = getApp();
const time = util.formatTime(new Date);
const dateNow = [time.year, time.month].map(util.formatNumber).join("-");
var lineChart = null;
var startPos = null;

function getDays(year, month) {
  const dayArr = [0, 31, ((year % 4 === 0) && (year % 100 !== 0) || (year % 400 == 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
  return dayArr[month];
}

function getWeek(y, m, d) {
  var date = new Date();
  date.setFullYear(y);
  date.setMonth(m);
  date.setDate(d);
  return date.getDay() === 0 ? 7 : date.getDay();
}
Page({
  data: {
    dateNow: dateNow,
    date: dateNow,
    recordsNow: [],
    week: [],
    daysAt: 0,
    daysAb: 0,
    daysAbsence: 0,
    flex: 1,
  },
  onLoad: function(e) {
    var that = this
    wx.getStorage({
      key: 'flex',
      success: function(res) {
        that.setData({
          flex: res.data,
        })
      },
    })
    that.request()
  },
  touchHandler: function(e) {
    lineChart.scrollStart(e);
  },
  moveHandler: function(e) {
    lineChart.scroll(e);
  },
  touchEndHandler: function(e) {
    lineChart.scrollEnd(e);
    lineChart.showToolTip(e, {
      format: function(item, category) {
        if (e) {
          return item.name + category + '号: ' + item.data + '分钟'
        }
      }
    });
  },
  createSimulationData: function(objArr) {
    var data = [];
    for (let i = 0; i < 31; i++) {
      //处理两个月天数不同导致，tips报错不显示的问题
      //判断是否有那一天的数据，否则填入空
      //可能这个月不存在那一天，可能后台并没有那一天的数据
      if (objArr[i] && objArr[i].duty !== -1) {
        data.push(objArr[i].duty)
      } else {
        data.push(null)
      }
    }
    return {
      data
    }
  },
  request: function() {
    var that = this
    util.requestGet({
      url: "/api/Wx/myCheck",
      data: {
        month: that.data.date,
        access_token: app.globalData.userInfo.token
      },
    }).then(function(res) {
        const LAST_RECORDS = res.data.data.lastRecords; //上个月的原始数据
        const RECORDS = res.data.data.records; //当月的原始数据
        //建立日历模型对象数组
        const YEAR_SELECT = parseInt(that.data.date.split('-')[0]); //所选的年份

        const MONTH_SELECT = parseInt(that.data.date.split('-')[1]); //所选的月份
        const MONTH_LAST = MONTH_SELECT - 1 === 0 ? 12 : MONTH_SELECT - 1;
        const MONTH_NEXT = MONTH_SELECT + 1 === 13 ? 12 : MONTH_SELECT + 1;

        const DAYS_LAST = getDays(YEAR_SELECT, MONTH_LAST); //上个月的天数
        const DAYS_SELECT = getDays(YEAR_SELECT, MONTH_SELECT); //所选月的天数
        const DAYS_NEXT = getDays(YEAR_SELECT, MONTH_NEXT); //下个月的天数

        const WEEK_SELECT_FIRST = getWeek(YEAR_SELECT, MONTH_SELECT - 1, 1); //所选月份的第一天的星期数
        const WEEK_SELECT_LAST = getWeek(YEAR_SELECT, MONTH_SELECT - 1, DAYS_SELECT); //所选月份的最后一天的星期数
        var monthLast = [],
          monthNext = [],
          monthSelect = [],
          daysAt = 0,
          daysAb = 0,
          daysAbsence = 0;
        //1.确定上个月补充天数，填充数组
        for (let i = DAYS_LAST; i > DAYS_LAST - WEEK_SELECT_FIRST + 1; i--) {
          var temp = {};
          temp.month = MONTH_LAST;
          temp.day = i;
          temp.state = 0;
          temp.duty = 0;
          monthLast.push(temp);
        }
        monthLast.reverse();
        //2.确定所选月份的天数，填充数组
        for (let i = 1; i <= DAYS_SELECT; i++) {
          var temp = {};
          temp.month = MONTH_SELECT;
          temp.day = i;
          temp.state = 0;
          temp.duty = -1;
          monthSelect.push(temp);
        }
        //3.确定下个月补充天数，填充数组
        for (let i = 1; i < 7 - WEEK_SELECT_LAST + 1; i++) {
          var temp = {};
          temp.month = MONTH_NEXT;
          temp.day = i;
          temp.state = 0;
          temp.duty = 0;
          monthNext.push(temp);
        }

        //处理原始数据 建立哈希表，将日期作为键，状态作为值
        var hash = new Object();
        RECORDS.forEach(function(val, idx) {
          //转化考勤状态
          var temp_state = 2;
          //1.达标；2.不达标，-1.旷工，0.默认
          if (val.absence > 0) {
            if (val.duty > 0) {
              temp_state = 2;
            } else {
              temp_state = -1;
            }
          } else {
            if (val.duty > 0) {
              temp_state = 1;
            } else {
              temp_state = 0;
            }
          }
          var temp_date = val.date.slice(val.date.indexOf('-') + 1); //去除年份
          hash[temp_date] = temp_state + '&' + val.duty;
        })
        //填入哈希表数据进入日历模型对象数组
        monthLast.forEach(function(val) {
          var date = [val.month > 9 ? val.month : '0' + val.month, val.day > 9 ? val.day : '0' + val.day].join('-')
          val.state = parseInt(String(hash[date]).split('&')[0]) || 0;
          val.duty = parseInt(String(hash[date]).split('&')[1]) || 0;
        })
        
        monthSelect.forEach(function(val) {
          var date = [val.month > 9 ? val.month : '0' + val.month, val.day > 9 ? val.day : '0' + val.day].join('-')
          val.state = parseInt(String(hash[date]).split('&')[0]) || 0;
          if (date in hash) {
            val.duty = parseInt(String(hash[date]).split('&')[1]);
          }
        })

        monthNext.forEach(function(val) {
          var date = [val.month > 9 ? val.month : '0' + val.month, val.day > 9 ? val.day : '0' + val.day].join('-')
          val.state = parseInt(String(hash[date]).split('&')[0]) || 0;
          val.duty = parseInt(String(hash[date]).split('&')[1]) || 0;
        })
        //合并模型
        var recordsNow = [...monthLast, ...monthSelect, ...monthNext];
        //再处理月份
        recordsNow.forEach(function(val) {
          if (val.month === MONTH_SELECT) {
            val.month = 1;
            if (val.state === 1) {
              daysAt++;
            } else if (val.state === 2) {
              daysAb++;
            } else if (val.state === -1) {
              daysAbsence++;
            }
          } else {
            val.month = -1;
          }
        })


        //折线图
        var lastMonth = [];
        //建立上个月的数据模型
        for (let i = 1; i <= DAYS_LAST; i++) {
          var temp = {};
          temp.month = MONTH_LAST;
          temp.day = i;
          temp.state = 0;
          temp.duty = -1;
          lastMonth.push(temp);
        }

        //将上个月转化成哈希表
        var lastHash = new Object();
        LAST_RECORDS.forEach(function(val) {
          var temp_date = val.date.slice(val.date.indexOf('-') + 1); //去除年份
          lastHash[temp_date] = val.duty;
        })
        //模型中填入哈希表数据
        lastMonth.forEach(function(val) {
          var date = [val.month > 9 ? val.month : '0' + val.month, val.day > 9 ? val.day : '0' + val.day].join('-')
          if (date in lastHash) {
            val.duty = lastHash[date];
          }
        })
        // console.log("上个月原始数据", lastMonth)
        // console.log("当月原始数据", monthSelect)
        var chartLast = that.createSimulationData(lastMonth), //上月考勤时长
          chartSelcet = that.createSimulationData(monthSelect); //当月考勤时长
        // console.log("上个月", chartLast);
        // console.log("当月", chartSelcet);
        that.setData({
          recordsNow: recordsNow,
          daysAt: daysAt,
          daysAb: daysAb,
          daysAbsence: daysAbsence,
          mNumL: MONTH_LAST,
          mNumS: MONTH_SELECT,
        })
        //绘图
        that.chartDraw({
          mNumL: MONTH_LAST,
          mNumS: MONTH_SELECT,
          chartLast: chartLast,
          chartSelcet: chartSelcet,
        })
      },
      function(res) {
        that.setData({
          recordsNow: null,
          daysAt: 0,
          daysAb: 0,
          daysAbsence: 0,
          mNumL: '',
          mNumS: '',
        });
        wx.showToast({
          title: res.data.msg,
          icon: "none"
        });
      }).catch(function(res) {
      console.log(res)
    })
  },
  chartDraw: function(obj) {
    var that = this;
    var windowWidth = 320;
    try {
      var res = wx.getSystemInfoSync();
      windowWidth = res.windowWidth;
    } catch (e) {
      console.error('getSystemInfoSync failed!');
    }
    lineChart = new wxCharts({
      canvasId: 'lineCanvas',
      type: 'line',
      categories: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31],
      animation: true,
      series: [{
        name: obj.mNumL + '月',
        color: "#fbbe05",
        data: obj.chartLast.data,
        format: function(val, name) {
          return val;
        }
      }, {
        name: obj.mNumS + '月',
        color: "#4486f4",
        data: obj.chartSelcet.data,
        format: function(val, name) {
          return val;
        }
      }],
      xAxis: {
        type: 'calibration',
      },
      yAxis: {
        title: '考勤时长(分钟)',
        format: function(val) {
          return val.toFixed();
        },
        min: 0
      },
      width: windowWidth,
      height: 300,
      dataLabel: true,
      dataPointShape: true,
      enableScroll: true,
      //[1,2)
      flex: that.data.flex,
      extra: {
        lineStyle: 'curve'
      }
    });
  },
  flex: function(e) {
    var that = this,
      flex = that.data.flex;
    if (e.currentTarget.dataset.state === 'minus') {
      flex -= 0.1;
      flex = flex <= 1 ? 1 : flex;
    } else {
      flex += 0.1;
      flex = flex >= 2 ? 1.9 : flex;
    }
    //节流
    setTimeout(function() {
      that.setData({
        flex: flex,
      });
      lineChart.updateData({
        flex: flex,
      });
      wx.setStorage({
        key: 'flex',
        data: flex,
      })
    }, 200)
  },
  bindDateChange: function(e) {
    var that = this;
    that.setData({
      date: e.detail.value
    })
    that.request();
  },
  dateUp: function() {
    var that = this,
      year = that.data.date.split('-')[0],
      month = that.data.date.split('-')[1];
    month--;
    if (month === 0) {
      month = 12;
      year--;
    }
    that.setData({
      date: [year, util.formatNumber(month)].join('-')
    })
    that.request();
  },
  dateDown: function() {
    var that = this;
    if (dateNow === that.data.date) {
      wx.showToast({
        title: '你想穿越到未来吗？',
        icon: 'none'
      })
    } else {
      var that = this,
        year = that.data.date.split('-')[0],
        month = that.data.date.split('-')[1];
      month++;
      if (month > 12) {
        month = 1;
        year++;
      }
      that.setData({
        date: [year, util.formatNumber(month)].join('-')
      })
      that.request();
    }
  },
})