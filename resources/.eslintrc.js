module.exports = {
  root: true,
  parserOptions: {
   ecmaVersion: 2018
  },
  extends: [
    'plugin:vue/recommended'
  ],
  'rules': {
    'generator-star-spacing': 0,
    'no-debugger': process.env.NODE_ENV === 'production' ? 2 : 0
  },
  globals: {
    performance: true
  }
}
