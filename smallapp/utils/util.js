//加零处理
const app = getApp();
const weekday = ['日', '一', '二', '三', '四', '五', '六'];
const formatNumber = n => {
  n = n.toString()
  return n[1] ? n : '0' + n
}
//时间
const formatTime = date => {
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  const week = weekday[date.getDay()]
  const hour = date.getHours()
  const minute = date.getMinutes()
  const second = date.getSeconds()
  return {
    year,
    month,
    day,
    week,
    hour,
    minute,
    second
  }
}
//没必要写进全局
const formatBegin = date => {
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = 1

  return [year, month, day].map(formatNumber).join("-")
}
//图片转换
const base64 = url => {
  return wx.getFileSystemManager().readFileSync(url, 'base64');
}
//日期控制
const dateCtrl = ({
  time,
  ctrl = 'up'
} = {}) => {
  let year = time.slice(0, 4),
    month = time.slice(5, 7),
    day = time.slice(8, 10),
    dayArr = [0, 31, ((year % 4 === 0) && (year % 100 !== 0) || (year % 400 == 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
  if (ctrl === 'up') {
    //递减处理
    day = day - 1;
    month = day === 0 ? month - 1 : month;
    year = month === 0 ? year - 1 : year;
    //触底回顶
    month = month === 0 ? 12 : month;
    day = day === 0 ? dayArr[month] : day;
  } else if (ctrl === 'down') {
    //递增处理
    day = +day + 1;
    const oldMonth = month;
    month = day > dayArr[+month] ? +month + 1 : month;
    year = month > 12 ? +year + 1 : year;
    //触顶回底
    month = month > 12 ? 1 : month;
    day = day > dayArr[+oldMonth] ? 1 : day;
  }
  // console.log(year)
  // console.log(month)
  // console.log(day)
  return [year, month, day].map(formatNumber).join("-")
}

const formathour = array => {
  for (let i = 0; i < array.length; i++) {
    array[i] = parseFloat((array[i] / 60).toFixed(1))
  }
  return array
}
//get请求封装
const requestGet = ({
  url,
  data,
  header = {
    'Content-Type': 'application/json',
    'access_token': app.globalData.userInfo.token
  }
}) => {
  return request(url, 'GET', header, data);
}
//post请求封装
const requestPost = ({
  url,
  data,
  header = {
    'Content-Type': 'application/x-www-form-urlencoded',
    'access_token': app.globalData.userInfo.token
  }
}) => {
  return request(url, 'POST', header, data);
}
const request = (url, method, header, data) => {
  wx.showLoading({
    title: '加载中',
    icon: 'none'
  })
  return new Promise(function(resolve, reject) {
    wx.request({
      url: app.globalData.urlId + url,
      method: method,
      header: header,
      data: data,
      success: (res) => {
        console.log(res);
        if (res.data.code === 0) {
          resolve(res)
        } else {
          reject(res)
        }
      },
      fail: (res) => {
        reject(res)
      },
      complete: (res) => {
        wx.hideLoading();
      }
    })
  })
}

module.exports = {
  formatTime,
  formatBegin,
  base64,
  formatNumber,
  dateCtrl,
  formathour,
  requestGet,
  requestPost
}