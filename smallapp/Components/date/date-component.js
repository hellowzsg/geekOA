//固定选择时间范围
const util = require('../../utils/util.js')
const time = util.formatTime(new Date())
const dateNow = [time.year, time.month, time.day].map(util.formatNumber).join("-")
Component({
  properties: {
    date: {
      type: String,
      value: dateNow,
      observer: '_dateChange'
    },
    week: {
      type: String,
      value: time.week
    },
    dateNow: {
      type: String,
      value: dateNow,
    }
  },
  data: {
    date: dateNow,
    week: time.week
  },
  methods: {
    _dateChange: function(newVal, oldVal) {
      var dates = newVal.split('-'),
        time = new Date();
      time.setFullYear(dates[0]);
      time.setMonth(dates[1] - 1);
      time.setDate(dates[2]);
      this.setData({
        week: util.formatTime(time).week
      })
    },
    bindDateChange: function(e) {
      var that = this
      that.setData({
        date: e.detail.value,
      })
      that.triggerEvent('date', that.data)
    },
    dateUp: function() {
      var that = this;
      that.setData({
        date: util.dateCtrl({
          time: that.data.date,
          ctrl: 'up'
        })
      })
      that.triggerEvent('dateUp', that.data)
    },
    dateDown: function() {
      var that = this;
      if (dateNow === that.data.date) {
        wx.showToast({
          title: '你想穿越到未来吗？',
          icon: 'none'
        })
      } else {
        that.setData({
          date: util.dateCtrl({
            time: that.data.date,
            ctrl: 'down'
          })
        })
        that.triggerEvent('dateDown', that.data)
      }
    }
  },
})