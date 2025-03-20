function parse_url (str, component) { 
	// eslint-disable-line camelcase
	//       discuss at: http://locutus.io/php/parse_url/
	//      original by: Steven Levithan (http://blog.stevenlevithan.com)
	// reimplemented by: Brett Zamir (http://brett-zamir.me)
	//         input by: Lorenzo Pisani
	//         input by: Tony
	//      improved by: Brett Zamir (http://brett-zamir.me)
	//           note 1: original by http://stevenlevithan.com/demo/parseuri/js/assets/parseuri.js
	//           note 1: blog post at http://blog.stevenlevithan.com/archives/parseuri
	//           note 1: demo at http://stevenlevithan.com/demo/parseuri/js/assets/parseuri.js
	//           note 1: Does not replace invalid characters with '_' as in PHP,
	//           note 1: nor does it return false with
	//           note 1: a seriously malformed URL.
	//           note 1: Besides function name, is essentially the same as parseUri as
	//           note 1: well as our allowing
	//           note 1: an extra slash after the scheme/protocol (to allow file:/// as in PHP)
	//        example 1: parse_url('http://user:pass@host/path?a=v#a')
	//        returns 1: {scheme: 'http', host: 'host', user: 'user', pass: 'pass', path: '/path', query: 'a=v', fragment: 'a'}
	//        example 2: parse_url('http://en.wikipedia.org/wiki/%22@%22_%28album%29')
	//        returns 2: {scheme: 'http', host: 'en.wikipedia.org', path: '/wiki/%22@%22_%28album%29'}
	//        example 3: parse_url('https://host.domain.tld/a@b.c/folder')
	//        returns 3: {scheme: 'https', host: 'host.domain.tld', path: '/a@b.c/folder'}
	//        example 4: parse_url('https://gooduser:secretpassword@www.example.com/a@b.c/folder?foo=bar')
	//        returns 4: { scheme: 'https', host: 'www.example.com', path: '/a@b.c/folder', query: 'foo=bar', user: 'gooduser', pass: 'secretpassword' }

	var query

	var mode = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.parse_url.mode') : undefined) || 'php'

	var key = [
	'source',
	'scheme',
	'authority',
	'userInfo',
	'user',
	'pass',
	'host',
	'port',
	'relative',
	'path',
	'directory',
	'file',
	'query',
	'fragment'
	]

	// For loose we added one optional slash to post-scheme to catch file:/// (should restrict this)
	var parser = {
	php: new RegExp([
	  '(?:([^:\\/?#]+):)?',
	  '(?:\\/\\/()(?:(?:()(?:([^:@\\/]*):?([^:@\\/]*))?@)?([^:\\/?#]*)(?::(\\d*))?))?',
	  '()',
	  '(?:(()(?:(?:[^?#\\/]*\\/)*)()(?:[^?#]*))(?:\\?([^#]*))?(?:#(.*))?)'
	].join('')),
	strict: new RegExp([
	  '(?:([^:\\/?#]+):)?',
	  '(?:\\/\\/((?:(([^:@\\/]*):?([^:@\\/]*))?@)?([^:\\/?#]*)(?::(\\d*))?))?',
	  '((((?:[^?#\\/]*\\/)*)([^?#]*))(?:\\?([^#]*))?(?:#(.*))?)'
	].join('')),
	loose: new RegExp([
	  '(?:(?![^:@]+:[^:@\\/]*@)([^:\\/?#.]+):)?',
	  '(?:\\/\\/\\/?)?',
	  '((?:(([^:@\\/]*):?([^:@\\/]*))?@)?([^:\\/?#]*)(?::(\\d*))?)',
	  '(((\\/(?:[^?#](?![^?#\\/]*\\.[^?#\\/.]+(?:[?#]|$)))*\\/?)?([^?#\\/]*))',
	  '(?:\\?([^#]*))?(?:#(.*))?)'
	].join(''))
	}

	var m = parser[mode].exec(str)
	var uri = {}
	var i = 14

	while (i--) {
	if (m[i]) {
	  uri[key[i]] = m[i]
	}
	}

	if (component) {
	return uri[component.replace('PHP_URL_', '').toLowerCase()]
	}

	if (mode !== 'php') {
	var name = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.parse_url.queryKey') : undefined) || 'queryKey'
	parser = /(?:^|&)([^&=]*)=?([^&]*)/g
	uri[name] = {}
	query = uri[key[12]] || ''
	query.replace(parser, function ($0, $1, $2) {
	  if ($1) {
		uri[name][$1] = $2
	  }
	})
	}

	delete uri.source
	return uri
}
function parse_str (str, array) { 
	// eslint-disable-line camelcase
	//       discuss at: http://locutus.io/php/parse_str/
	//      original by: Cagri Ekin
	//      improved by: Michael White (http://getsprink.com)
	//      improved by: Jack
	//      improved by: Brett Zamir (http://brett-zamir.me)
	//      bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
	//      bugfixed by: Brett Zamir (http://brett-zamir.me)
	//      bugfixed by: stag019
	//      bugfixed by: Brett Zamir (http://brett-zamir.me)
	//      bugfixed by: MIO_KODUKI (http://mio-koduki.blogspot.com/)
	// reimplemented by: stag019
	//         input by: Dreamer
	//         input by: Zaide (http://zaidesthings.com/)
	//         input by: David Pesta (http://davidpesta.com/)
	//         input by: jeicquest
	//           note 1: When no argument is specified, will put variables in global scope.
	//           note 1: When a particular argument has been passed, and the
	//           note 1: returned value is different parse_str of PHP.
	//           note 1: For example, a=b=c&d====c
	//        example 1: var $arr = {}
	//        example 1: parse_str('first=foo&second=bar', $arr)
	//        example 1: var $result = $arr
	//        returns 1: { first: 'foo', second: 'bar' }
	//        example 2: var $arr = {}
	//        example 2: parse_str('str_a=Jack+and+Jill+didn%27t+see+the+well.', $arr)
	//        example 2: var $result = $arr
	//        returns 2: { str_a: "Jack and Jill didn't see the well." }
	//        example 3: var $abc = {3:'a'}
	//        example 3: parse_str('a[b]["c"]=def&a[q]=t+5', $abc)
	//        example 3: var $result = $abc
	//        returns 3: {"3":"a","a":{"b":{"c":"def"},"q":"t 5"}}

	var strArr = String(str).replace(/^&/, '').replace(/&$/, '').split('&')
	var sal = strArr.length
	var i
	var j
	var ct
	var p
	var lastObj
	var obj
	var undef
	var chr
	var tmp
	var key
	var value
	var postLeftBracketPos
	var keys
	var keysLen

	var _fixStr = function (str) {
	return decodeURIComponent(str.replace(/\+/g, '%20'))
	}

	var $global = (typeof window !== 'undefined' ? window : global)
	$global.$locutus = $global.$locutus || {}
	var $locutus = $global.$locutus
	$locutus.php = $locutus.php || {}

	if (!array) {
	array = $global
	}

	for (i = 0; i < sal; i++) {
	tmp = strArr[i].split('=')
	key = _fixStr(tmp[0])
	value = (tmp.length < 2) ? '' : _fixStr(tmp[1])

	while (key.charAt(0) === ' ') {
	  key = key.slice(1)
	}
	if (key.indexOf('\x00') > -1) {
	  key = key.slice(0, key.indexOf('\x00'))
	}
	if (key && key.charAt(0) !== '[') {
	  keys = []
	  postLeftBracketPos = 0
	  for (j = 0; j < key.length; j++) {
		if (key.charAt(j) === '[' && !postLeftBracketPos) {
		  postLeftBracketPos = j + 1
		} else if (key.charAt(j) === ']') {
		  if (postLeftBracketPos) {
			if (!keys.length) {
			  keys.push(key.slice(0, postLeftBracketPos - 1))
			}
			keys.push(key.substr(postLeftBracketPos, j - postLeftBracketPos))
			postLeftBracketPos = 0
			if (key.charAt(j + 1) !== '[') {
			  break
			}
		  }
		}
	  }
	  if (!keys.length) {
		keys = [key]
	  }
	  for (j = 0; j < keys[0].length; j++) {
		chr = keys[0].charAt(j)
		if (chr === ' ' || chr === '.' || chr === '[') {
		  keys[0] = keys[0].substr(0, j) + '_' + keys[0].substr(j + 1)
		}
		if (chr === '[') {
		  break
		}
	  }

	  obj = array
	  for (j = 0, keysLen = keys.length; j < keysLen; j++) {
		key = keys[j].replace(/^['"]/, '').replace(/['"]$/, '')
		lastObj = obj
		if ((key !== '' && key !== ' ') || j === 0) {
		  if (obj[key] === undef) {
			obj[key] = {}
		  }
		  obj = obj[key]
		} else {
		  // To insert new dimension
		  ct = -1
		  for (p in obj) {
			if (obj.hasOwnProperty(p)) {
			  if (+p > ct && p.match(/^\d+$/g)) {
				ct = +p
			  }
			}
		  }
		  key = ct + 1
		}
	  }
	  lastObj[key] = value
	}
	}
}
function number_format (number, decimals, decPoint, thousandsSep) { 
	// eslint-disable-line camelcase
	//  discuss at: http://locutus.io/php/number_format/
	// original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	// improved by: Kevin van Zonneveld (http://kvz.io)
	// improved by: davook
	// improved by: Brett Zamir (http://brett-zamir.me)
	// improved by: Brett Zamir (http://brett-zamir.me)
	// improved by: Theriault (https://github.com/Theriault)
	// improved by: Kevin van Zonneveld (http://kvz.io)
	// bugfixed by: Michael White (http://getsprink.com)
	// bugfixed by: Benjamin Lupton
	// bugfixed by: Allan Jensen (http://www.winternet.no)
	// bugfixed by: Howard Yeend
	// bugfixed by: Diogo Resende
	// bugfixed by: Rival
	// bugfixed by: Brett Zamir (http://brett-zamir.me)
	//  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	//  revised by: Luke Smith (http://lucassmith.name)
	//    input by: Kheang Hok Chin (http://www.distantia.ca/)
	//    input by: Jay Klehr
	//    input by: Amir Habibi (http://www.residence-mixte.com/)
	//    input by: Amirouche
	//   example 1: number_format(1234.56)
	//   returns 1: '1,235'
	//   example 2: number_format(1234.56, 2, ',', ' ')
	//   returns 2: '1 234,56'
	//   example 3: number_format(1234.5678, 2, '.', '')
	//   returns 3: '1234.57'
	//   example 4: number_format(67, 2, ',', '.')
	//   returns 4: '67,00'
	//   example 5: number_format(1000)
	//   returns 5: '1,000'
	//   example 6: number_format(67.311, 2)
	//   returns 6: '67.31'
	//   example 7: number_format(1000.55, 1)
	//   returns 7: '1,000.6'
	//   example 8: number_format(67000, 5, ',', '.')
	//   returns 8: '67.000,00000'
	//   example 9: number_format(0.9, 0)
	//   returns 9: '1'
	//  example 10: number_format('1.20', 2)
	//  returns 10: '1.20'
	//  example 11: number_format('1.20', 4)
	//  returns 11: '1.2000'
	//  example 12: number_format('1.2000', 3)
	//  returns 12: '1.200'
	//  example 13: number_format('1 000,50', 2, '.', ' ')
	//  returns 13: '100 050.00'
	//  example 14: number_format(1e-8, 8, '.', '')
	//  returns 14: '0.00000001'

	number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
	var n = !isFinite(+number) ? 0 : +number
	var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
	var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
	var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
	var s = ''

	var toFixedFix = function (n, prec) {
	var k = Math.pow(10, prec)
	return '' + (Math.round(n * k) / k)
	  .toFixed(prec)
	}

	// @todo: for IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.')
	if (s[0].length > 3) {
	s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
	}
	if ((s[1] || '').length < prec) {
	s[1] = s[1] || ''
	s[1] += new Array(prec - s[1].length + 1).join('0')
	}

	return s.join(dec)
}