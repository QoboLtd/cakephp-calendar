module.exports = {
  "moduleFileExtensions": [
    "js",
    "json",
    "vue"
  ],
  "testPathIgnorePatterns": [
    "/node_modules/"
  ],
  "transform": {
    ".*\\.(vue)$": "vue-jest",
    "^.+\\.js$": "<rootDir>/node_modules/babel-jest"
  },
  "moduleNameMapper": {
    "^@/(.*)$": "<rootDir>/resources/src/$1"
  }
}
