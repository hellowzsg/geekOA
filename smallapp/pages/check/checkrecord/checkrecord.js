var util = require("../../../utils/util.js")
const app = getApp()
const time = util.formatTime(new Date())
const dateNow = [time.year, time.month, time.day].map(util.formatNumber).join("-")
var compare = function(prop) {
  return function(a, b) {
    var x = a[prop],
      y = b[prop];
    if (x > y) return -1;
    else return 1;
    return 0;
  }
}
Page({
  data: {
    record: {
      at: [],
      ab: [],
      absence: [],
      statis: {
        total: '',
        at: '',
        ab: '',
        absence: ''
      },
    },
    currentTab: -1,
    isRecord: false,
    isAt: false,
    isAb: false,
    isAbt: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    var date,
      that = this,
      time = util.formatTime(new Date());
    date = [time.year, time.month, time.day].map(util.formatNumber).join("-")
    that.setData({
      date: date
    })
    that.request()
  },

  request: function(e) {
    var that = this
    util.requestGet({
      url: '/api/Wx/record',
      data: {
        date: e ? e.detail.date : dateNow,
        access_token: app.globalData.userInfo.token
      }
    }).then(function(res) {
      var at = [],
        ab = [],
        absence = [];
      res.data.data.forEach(function(item, index) {
        if (item.duty > 0) {
          at.push(item);
          if (item.absence > 0)
            ab.push(item);
        } else {
          if (item.absence > 0)
            absence.push(item);
        }
      })
      at.sort(compare('duty'))
      ab.sort(compare('absence'))
      // console.log(at)
      // console.log(ab)
      // console.log(absence)
      that.setData({
        record: {
          at: at,
          ab: ab,
          absence: absence,
          statis: {
            total: res.data.data.length,
            at: at.length,
            ab: ab.length,
            absence: absence.length
          },
        },
        isRecord: true
      })
    }, function(res) {
      that.setData({
        record: {},
        isRecord: false
      })
      wx.showToast({
        title: res.data.msg,
        icon: "none"
      })
    })
  },
  seeMore: function(e) {
    var that = this
    if (e.currentTarget.dataset.is == 'at') {
      that.setData({
        isAt: !that.data.isAt,
      })
    } else if (e.currentTarget.dataset.is == 'ab') {
      that.setData({
        isAb: !that.data.isAb,
      })
    } else if (e.currentTarget.dataset.is == 'abt') {
      that.setData({
        isAbt: !that.data.isAbt,
      })
    }
  },
  detailAt: function(e) {
    wx.navigateTo({
      url: './detail/detail?aid=' + e.currentTarget.dataset.aid,
    })
  },

  detailAb: function(e) {
    wx.navigateTo({
      url: './explain/explain?aid=' + e.currentTarget.dataset.aid + '&date=' + e.currentTarget.dataset.date + '&absence=' + e.currentTarget.dataset.absence + '&remark=' + e.currentTarget.dataset.remark + '&uid=' + e.currentTarget.dataset.uid,
    })
  }
})