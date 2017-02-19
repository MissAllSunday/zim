<?php

namespace Services;

class Tools extends \Prefab
{
	function __construct(\Base $f3)
	{
		$this->f3 = $f3;
	}

	function slug($str = '')
	{
		if (empty($str) || !is_string($str))
			return '';

		// Make sure string is in UTF-8 and strip invalid UTF-8 characters
		$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

		// @todo build an admin setting for this.
		$options = array(
			'delimiter' => '-',
			'limit' => 250,
			'lowercase' => true,
			'replacements' => array(),
			'transliterate' => false,
		);

		$char_map = array(
			// Latin
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
			'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
			'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
			'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
			'ß' => 'ss',
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
			'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
			'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
			'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
			'ÿ' => 'y',
			// Latin symbols
			'©' => '(c)',
			// Greek
			'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
			'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
			'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
			'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
			'Ϋ' => 'Y',
			'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
			'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
			'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
			'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
			'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
			// Turkish
			'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
			'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
			// Russian
			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
			'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
			'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
			'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
			'Я' => 'Ya',
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
			'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
			'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
			'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
			'я' => 'ya',
			// Ukrainian
			'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
			'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
			// Czech
			'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
			'Ž' => 'Z',
			'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
			'ž' => 'z',
			// Polish
			'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
			'Ż' => 'Z',
			'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
			'ż' => 'z',
			// Latvian
			'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
			'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
			'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
			'š' => 's', 'ū' => 'u', 'ž' => 'z'
		);

		// Make custom replacements
		$str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

		// Transliterate characters to ASCII
		if ($options['transliterate'])
			$str = str_replace(array_keys($char_map), $char_map, $str);

		// Replace non-alphanumeric characters with our delimiter
		$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

		// Remove duplicate delimiters
		$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

		// Truncate slug to max. characters
		$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

		// Remove delimiter from ends
		$str = trim($str, $options['delimiter']);

		return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
	}

	function metaKeywords($tags = [])
	{
		$commonWords = array('a','able','about','above','abroad','according','accordingly','across','actually','adj','after','afterwards','again','against','ago','ahead','ain\'t','all','allow','allows','almost','alone','along','alongside','already','also','although','always','am','amid','amidst','among','amongst','an','and','another','any','anybody','anyhow','anyone','anything','anyway','anyways','anywhere','apart','appear','appreciate','appropriate','are','aren\'t','around','as','a\'s','aside','ask','asking','associated','at','available','away','awfully','b','back','backward','backwards','be','became','because','become','becomes','becoming','been','before','beforehand','begin','behind','being','believe','below','beside','besides','best','better','between','beyond','both','brief','but','by','c','came','can','cannot','cant','can\'t','caption','cause','causes','certain','certainly','changes','clearly','c\'mon','co','co.','com','come','comes','concerning','consequently','consider','considering','contain','containing','contains','corresponding','could','couldn\'t','course','c\'s','currently','d','dare','daren\'t','definitely','described','despite','did','didn\'t','different','directly','do','does','doesn\'t','doing','done','don\'t','down','downwards','during','e','each','edu','eg','eight','eighty','either','else','elsewhere','end','ending','enough','entirely','especially','et','etc','even','ever','evermore','every','everybody','everyone','everything','everywhere','ex','exactly','example','except','f','fairly','far','farther','few','fewer','fifth','first','five','followed','following','follows','for','forever','former','formerly','forth','forward','found','four','from','further','furthermore','g','get','gets','getting','given','gives','go','goes','going','gone','got','gotten','greetings','h','had','hadn\'t','half','happens','hardly','has','hasn\'t','have','haven\'t','having','he','he\'d','he\'ll','hello','help','hence','her','here','hereafter','hereby','herein','here\'s','hereupon','hers','herself','he\'s','hi','him','himself','his','hither','hopefully','how','howbeit','however','hundred','i','i\'d','ie','if','ignored','i\'ll','i\'m','immediate','in','inasmuch','inc','inc.','indeed','indicate','indicated','indicates','inner','inside','insofar','instead','into','inward','is','isn\'t','it','it\'d','it\'ll','its','it\'s','itself','i\'ve','j','just','k','keep','keeps','kept','know','known','knows','l','last','lately','later','latter','latterly','least','less','lest','let','let\'s','like','liked','likely','likewise','little','look','looking','looks','low','lower','ltd','m','made','mainly','make','makes','many','may','maybe','mayn\'t','me','mean','meantime','meanwhile','merely','might','mightn\'t','mine','minus','miss','more','moreover','most','mostly','mr','mrs','much','must','mustn\'t','my','myself','n','name','namely','nd','near','nearly','necessary','need','needn\'t','needs','neither','never','neverf','neverless','nevertheless','new','next','nine','ninety','no','nobody','non','none','nonetheless','noone','no-one','nor','normally','not','nothing','notwithstanding','novel','now','nowhere','o','obviously','of','off','often','oh','ok','okay','old','on','once','one','ones','one\'s','only','onto','opposite','or','other','others','otherwise','ought','oughtn\'t','our','ours','ourselves','out','outside','over','overall','own','p','particular','particularly','past','per','perhaps','placed','please','plus','possible','presumably','probably','provided','provides','q','que','quite','qv','r','rather','rd','re','really','reasonably','recent','recently','regarding','regardless','regards','relatively','respectively','right','round','s','said','same','saw','say','saying','says','second','secondly','see','seeing','seem','seemed','seeming','seems','seen','self','selves','sensible','sent','serious','seriously','seven','several','shall','shan\'t','she','she\'d','she\'ll','she\'s','should','shouldn\'t','since','six','so','some','somebody','someday','somehow','someone','something','sometime','sometimes','somewhat','somewhere','soon','sorry','specified','specify','specifying','still','sub','such','sup','sure','t','take','taken','taking','tell','tends','th','than','thank','thanks','thanx','that','that\'ll','thats','that\'s','that\'ve','the','their','theirs','them','themselves','then','thence','there','thereafter','thereby','there\'d','therefore','therein','there\'ll','there\'re','theres','there\'s','thereupon','there\'ve','these','they','they\'d','they\'ll','they\'re','they\'ve','thing','things','think','third','thirty','this','thorough','thoroughly','those','though','three','through','throughout','thru','thus','till','to','together','too','took','toward','towards','tried','tries','truly','try','trying','t\'s','twice','two','u','un','under','underneath','undoing','unfortunately','unless','unlike','unlikely','until','unto','up','upon','upwards','us','use','used','useful','uses','using','usually','v','value','various','versus','very','via','viz','vs','w','want','wants','was','wasn\'t','way','we','we\'d','welcome','well','we\'ll','went','were','we\'re','weren\'t','we\'ve','what','whatever','what\'ll','what\'s','what\'ve','when','whence','whenever','where','whereafter','whereas','whereby','wherein','where\'s','whereupon','wherever','whether','which','whichever','while','whilst','whither','who','who\'d','whoever','whole','who\'ll','whom','whomever','who\'s','whose','why','will','willing','wish','with','within','without','wonder','won\'t','would','wouldn\'t','x','y','yes','yet','you','you\'d','you\'ll','your','you\'re','yours','yourself','yourselves','you\'ve','z','zero');

		return $this->commaSeparated(implode(', ', array_diff($tags, $commonWords)), 'alpha');
	}

	function metaDescription($str = '', $limit = 150)
	{
		$str = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags(str_replace('&#39', '\'', $str)), ENT_NOQUOTES)))));

		if (strlen ($str) > $limit)
		{
			$str = substr ($str, 0, $limit - 3);
			return (substr ($str, 0, strrpos ($str, ' ')).'...');
		}

		return $str;
	}

	function sanitize($str = '')
	{
		$config = \HTMLPurifier_Config::createDefault();
		$config->set('Core', 'Encoding', 'UTF-8');
		$def = $config->getHTMLDefinition(true);
		$meta = $def->addElement(
			'meta',
			'Inline',
			'Empty',
			'Common',
			[
				'itemprop' => 'Text',
				'content' => 'URI',
				'itemscope' => 'Bool',
				'itemtype' => 'URI',
			]
		);
		$def->addAttribute('div', 'itemprop', 'Text');
		$def->addAttribute('div', 'itemscope', 'Bool');
		$def->addAttribute('div', 'itemtype', 'URI');
		$def->addAttribute('div', 'content', 'URI');
		$def->addAttribute('div', 'data-ohara_youtube', 'CDATA');

		$purifier = new \HTMLPurifier($config);

		return $purifier->purify($str);
	}

	function randomString($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++)
		$randomString .= $characters[rand(0, $charactersLength - 1)];

	return $randomString;
}

	/**
	 * Checks and returns a comma separated string.
	 * @access public
	 * @param string $string The string to check and format
	 * @param string $type The type to check against. Accepts "numeric", "alpha" and "alphanumeric".
	 * @param string $delimiter Used for explode/imploding the string.
	 * @return string|bool
	 */
	public function commaSeparated($string, $type = 'alphanumeric', $delimiter = ',')
	{
		if (empty($string))
			return false;

		switch ($type) {
			case 'numeric':
				$t = '\d';
				break;
			case 'alpha':
				$t = '[:alpha:]';
				break;
			case 'alphanumeric':
			default:
				$t = '[:alnum:]';
				break;
		}
		return empty($string) ? false : implode($delimiter, array_filter(explode($delimiter, preg_replace(
			array(
				'/[^'. $t .',]/',
				'/(?<='. $delimiter .')'. $delimiter .'+/',
				'/^'. $delimiter .'+/',
				'/'. $delimiter .'+$/'
			), '', $string
		))));
	}

	/**
	 * Returns a formatted string.
	 * @access public
	 * @param string|int  $bytes A number of bytes.
	 * @param bool $showUnits To show the unit symbol or not.
	 * @param int  $log the log used, either 1024 or 1000.
	 * @return string
	 */
	public function formatBytes($bytes, $showUnits = false, $log = 1024)
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log($log));
		$pow = min($pow, count($units) - 1);
		$bytes /= (1 << (10 * $pow));
		return round($bytes, 2) . ($showUnits ? ' ' . $units[$pow] : '');
	}

	public function parser($str = '')
	{
		$f3 = \Base::instance();

		// Youtube.
		$str =  preg_replace_callback(
			'~(?<=[\s>\.(;\'"]|^)(?:http|https):\/\/[\w\-_%@:|]?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/watch\?.+&v=))([\w-]{11})(?:[^\s]+)?(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>|<\/a> ))[?=&+%\w.-]*[\/\w\-_\~%@\?;=#}\\\\]?~ix',
			function ($matches)
			{
				if (!empty($matches) && !empty($matches[1]))
				{
					$params = urlencode(json_encode(['video_id' => $matches[1], 'title' => '']));

					return '<div class="oharaEmbed youtube" data-ohara_youtube="'. $params .'" id="oh_youtube_'. $matches[1] .'"></div>';
				}
			},
			$str
		);

		$find = [];
		$replace = [];
		$base = strip_tags(preg_replace(
			['"<a href(.*?)>(.*?)<"', '"/a>"', '"img (.*?)>"'],
			['','',''],
			$str
		));
		$noFollow = $f3->get('currentUser')->userID ? '' : 'rel="nofollow"';

		// Monster regex is monster.
		 preg_match_all(
			'/(?:(?:(?:
\b[a-z][\w\-]+:|(?<=^|\W)(?=\/\/))(?:\/\/(?:localhost|[\p{L}\p{M}\p{N}\-.:@]+\.(?:(?>xxx|qa|a(?>c|d|e(?>ro|)|f|g|i|l|m|n|o|q|r|s(?>ia|)|t|u|w|x|z)|b(?>a|b|d|e|f|g|h|i(?>z|)|j|m|n|o|r|s|t|v|w|y|z)|c(?>a(?>t|)|c|d|f|g|h|i|k|l|m|n|o(?>op|m|)|r|s|u|v|x|y|z)|d(?>d|e|j|k|m|o|z)|e(?>du|c|e|g|h|r|s|t|u)|f(?>i|j|k|m|o|r)|g(?>ov|a|b|d|e|f|g|h|i|l|m|n|p|q|r|s|t|u|w|y)|h(?>k|m|n|r|t|u)|i(?>d|e|l|m|n(?>fo|t|)|o|q|r|s|t)|j(?>a|e|m|o(?>bs|)|p)|k(?>e|g|h|i|m|n|p|r|w|y|z)|l(?>a|b|c|i|k|r|s|t|u|v|y)|m(?>il|a|c|d|e|g|h|k|l|m|n|o(?>bi|)|p|q|r|s|t|u(?>seum|)|v|w|x|y|z)|n(?>a(?>me|)|c|e(?>t|)|f|g|i|l|o|p|r|u|z)|o(?>rg|m)|p(?>ost|a|e|f|g|h|k|l|m|n|r(?>o|)|s|t|w|y)|r(?>e|o|s|u|w)|s(?>a|b|c|d|e|g|h|i|j|k|l|m|n|o|r|s|t|u|v|x|y|z)|t(?>el|c|d|f|g|h|j|k|l|m|n|o|p|r(?>avel|)|t|v|w|z)|u(?>a|g|k|s|y|z)|v(?>a|c|e|g|i|n|u)|w(?>f|s)|y(?>e|t|u)|z(?>a|m|w))|local))(?=[^\p{L}\p{N}\-.]|$)|
[\p{L}\p{N}][\p{L}\p{M}\p{N}\-.:@]+[\p{L}\p{M}\p{N}]\.[\p{L}\p{M}\p{N}\-]+))|(?:(?<=^|[^\p{L}\p{M}\p{N}\-:@])[\p{L}\p{N}][\p{L}\p{M}\p{N}\-.]+[\p{L}\p{M}\p{N}]\.(?>xxx|qa|a(?>c|d|e(?>ro|)|f|g|i|l|m|n|o|q|r|s(?>ia|)|t|u|w|x|z)|b(?>a|b|d|e|f|g|h|i(?>z|)|j|m|n|o|r|s|t|v|w|y|z)|c(?>a(?>t|)|c|d|f|g|h|i|k|l|m|n|o(?>op|m|)|r|s|u|v|x|y|z)|d(?>d|e|j|k|m|o|z)|e(?>du|c|e|g|h|r|s|t|u)|f(?>i|j|k|m|o|r)|g(?>ov|a|b|d|e|f|g|h|i|l|m|n|p|q|r|s|t|u|w|y)|h(?>k|m|n|r|t|u)|i(?>d|e|l|m|n(?>fo|t|)|o|q|r|s|t)|j(?>a|e|m|o(?>bs|)|p)|k(?>e|g|h|i|m|n|p|r|w|y|z)|l(?>a|b|c|i|k|r|s|t|u|v|y)|m(?>il|a|c|d|e|g|h|k|l|m|n|o(?>bi|)|p|q|r|s|t|u(?>seum|)|v|w|x|y|z)|n(?>a(?>me|)|c|e(?>t|)|f|g|i|l|o|p|r|u|z)|o(?>rg|m)|p(?>ost|a|e|f|g|h|k|l|m|n|r(?>o|)|s|t|w|y)|r(?>e|o|s|u|w)|s(?>a|b|c|d|e|g|h|i|j|k|l|m|n|o|r|s|t|u|v|x|y|z)|t(?>el|c|d|f|g|h|j|k|l|m|n|o|p|r(?>avel|)|t|v|w|z)|u(?>a|g|k|s|y|z)|v(?>a|c|e|g|i|n|u)|w(?>f|s)|y(?>e|t|u)|z(?>a|m|w))(?=$|[^\p{L}\p{N}\-]|\.(?=$|[^\p{L}\p{N}\-]))))(?:\/(?:(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’\/]|(?<!\/)\/))?)?/xiu',$base, $matches, PREG_PATTERN_ORDER);

		if (!empty($matches[0]))
		{
			foreach ($matches[0] as $match)
			{
				$scheme = parse_url($match, PHP_URL_SCHEME);
				$find[] = $match;
				$replace[] = '<a href="'. $match .'" '. $noFollow .'>'. str_replace($scheme .'://', '', $match) .'</a>';
			}

			$str = str_replace($find, $replace, $str);
		}

		unset($base);
		return $str;
	}
}
