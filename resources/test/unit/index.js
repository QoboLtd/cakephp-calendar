import Vue from 'vue'

Vue.config.productionTip = false

// require all src files for coverage.
// you can also change this to match only the subset of files that
// you want coverage for.
const srcContext = require.context('../src', true, /^\.\/(?!index(\.js)?$)/)
srcContext.keys().forEach(srcContext)

// run the tests inside div elements
before(function () {
  const el = document.createElement('DIV')
  el.id = 'tests'
  document.body.appendChild(el)
})

// remove div elements after each test run
after(function () {
  const el = document.getElementById('tests')
  for (let i = 0; i < el.children.length; ++i) {
    el.removeChild(el.children[i])
  }
})

// require all test files (files that ends with .spec.js)
const testsContext = require.context('./specs', true, /\.spec$/)
testsContext.keys().forEach(testsContext)
