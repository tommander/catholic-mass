# Documentation

Most important thing first: if there is something missing, irrelevant, unclear or incorrect in this documentation, please let me know. The same goes for the code. You can either create an issue or create a pull request. Thank you in advance :)

## Basic info

The main file is `index.php` which contains the HTML template of the app's (only) page. The functionality of translation JSON to HTML (and all related stuff) can be found in `massdata.php`. The main CSS file is `style.css`, while `fonts.css` just imports the "Source Code Pro", "Source Sans Pro" and "Source Serif Pro" fonts. Other files are not relevant for this documentation and I assume you can somehow discover, what is their purpose.

As for the directories, `data` contains language (translation) files, `images` contain guess what, `fontawesome` is the Fontawesome v5.15.4 distribution and `source-*` are the Source Code/Sans/Serif Pro font files.

## Language files

All language files can be found in the `data` directory. Every language has its own JSON file and then a record in the `langlist.json` file.

### Langlist.json

This file contains a list of all available languages and basic information about them, so that it is not needed to load all files for each visit of the site.

The file is structured as follows:

    {
		"language": {
			"title": string,
			"author": string,
			"link": string|array[string]
		}
	}

 - **Language** = three-letter code of the language (see [ISO 639-2 on Wikipedia](https://en.wikipedia.org/wiki/ISO_639-2))
 - **Title** = name of the language in that language
 - **Author** = author(s) of the translation
 - **Link** = single link (string) or more link (array of string) to the sources of the translation for the purpose of proper ownership and license attribution

### lng.json

This file contains the translation of the web and the order of mass in a particular language (or its dialect, if that's the case).

Languages are uniquely identified by their ISO 639-2 three-letter code. This code, along with the suffix ".json", makes up the filename.

The content is a JSON with the following structure:

	{
		"labels": {
			"labelID": "Label translation"
		},
		"texts": [
			object|array
		]
	}

#### Labels

Labels are those texts that are not a part of the mass script, but descriptors or webpage texts. This way you can see the webpage in your own language, while having the mass in a different language, so that you (sort of) know what is happening at any moment.

Each language file should contain the same subset of labels, otherwise people will miss them. Really.

Here's the list of all `labelID` needed in a language file.

 - **html** - ISO 639-1 code of the language
 - **idxL** - heading of the list of labels' translations 
 - **idxT** - heading of the list of texts' translations
 - **alleluia** - translation of "Alleluia", placeholder for a song or proclamation 
 - **heading** - title of the webpage (translation of "Mass")
 - **prayer** - translation of "Prayer"
 - **silentPrayer** - translation of "Silent prayer"
 - **dbrlink** - link to readings in that language (preferably directly to "Closest Sunday" page or somewhere you don't have to do too many clicks to get the current reading)
 - **dbrtext** - Text of the link above,
 - **read1** - translation of "First reading", used as a placeholder for... guess what
 - **read2** - translation of "Second reading", also placeholder
 - **readE** - translation of "Proclaiming Gospel", placeholder
 - **psalm** - translation of "Psalm", placeholder
 - **homily** - translation of "Homily", placeholder
 - **intercess** - translation of "Intercessions", placeholder
 - **offertory** - translation of "Offertory", placeholder
 - **breakhost** - translation of "Breaking Host", placeholder
 - **holycomm** - translation of "Holy Communion", placeholder (when priest is offering the Body and Blood of Christ)
 - **announce** - translation of "Announcements", placeholder
 - **lblP** - translation of "Priest", explanation for the abbreviation "P"
 - **lblA** - translation of "All", explanation for the abbreviation "A"
 - **lblR** - translation of "Reader", explanation for the abbreviation "R"
 - **lstand** - translation of "Standing", command
 - **lsit** - translation of "Sitting", command
 - **lkneel** - translation of "Kneeling", command
 - **headerimg** - translation of "Header image based on", 
 - **icons** - translation of "Icons", footer label
 - **font** - translation of "Font", footer label
 - **texts** - translation of "Texts", footer label
 - **author** - translation of "Author", footer label
 - **license** - translation of "License", footer label
 - **source** - translation of "Source", footer label

Note that you can add also your own labels, if you're using them somewhere in the order of mass, up to you.

#### Texts

The section "texts" contains the order of mass. It is an array, where each item in the array is either an object (explained below) or an array, if there are more options available (e.g. creeds after homily).

The sequence of items in this array corresponds to the order of mass. Each language (as per national/cultural/local traditions) can have slight differences or some special parts within the mass, that's why this section is so open.

If I'm referring to an "object" in this section, it is an object with only one key-value pair.

	{"key": "value"}
	{string: string}

**Key** stands for "who says this text". It can be either "p" for priest, "a" for all, "r" for reader (1st, 2nd reading and psalm) or empty for a command.

**Value** is the actual spoken text or command. That text can contain two special constructs:

	@{somelabel}
	@icon{someicon}

**Somelabel** is an ID of a label as described above, while **someicon** is an ID of one of the following icons:

 - **cross** - to mark the Sign of the Cross
 - **bible** - to mark reading from the Bible
 - **bubble** - to mark homily
 - **peace** - to mark the sign of peace
 - **walk** - to mark the final blessing
 - **stand** - to mark the "Standing" command
 - **sit** - to mark the "Sitting" command
 - **kneel** - to mark the "Kneeling" command
 - **booklink** - to mark the link to external readings

Example:

	{
		"labels": {
			"hw": "Hello world"
		},
		"texts": [
			{"a": "@{hw} @icon{peace}"}
		]
	}

This will show on the page roughly as:

> Hello world ![Fontawesome regular icon for handshake](images/handshake.png)

Now I mentioned that the texts array may contain either an object or another array (let's call it subarray). Each item in the subarray can be either an object or an array of objects. Let's see an example

	...
	"texts": [
		{"p": "Let's begin with greetings,"},
		[
			{"a": "Hello"},
			[
				{"p": "Hello"},
				{"a": "Hi"}
			],
		],
		{"p": "Nice greetings."}
	]

This means the priest first says "Let's begin with greetings". Afterwards there are two options - either everyone says "Hello", or the priest first says "Hello" and everyone responds "Hi". No matter which of these two options was chosen, the priest eventually says "Nice greetings".

## Massdata.php (class MassData)

This class implements all the functionality to convert JSON language files to resulting HTML and some supporting features.

### Class variables

 - **$tl** (string, public) - current language of texts (three letter code, 'eng' by default)
 - **$ll** (string, public) - current language of labels (three letter code, 'eng' by default)
 - **$langs** (array, public) - content of `langlist.json` decoded into an associative array
 - **$labels** (array, public) - content of the 'labels' object in the current main language file
 - **$icons** (array, private) - translation of icon IDs to respective Font Awesome CSS classes

### Class methods

#### __construct()

Sets `$tl` and `$ll` and then loads content from language files to `$langs` and `$labels`.

#### replcbs(array $matches): string

Callback for the method `repl()`, which replaces label IDs with respective label texts.

This is a callback for [PHP function `preg_replace_callback_array`](https://www.php.net/manual/en/function.preg-replace-callback-array).

#### replcb(array $matches): string

Same as `replcbs()`, but wraps the text in the `span` html tag.

This is a callback for [PHP function `preg_replace_callback_array`](https://www.php.net/manual/en/function.preg-replace-callback-array).

#### replico(array $matches): string

Replace icon IDs with the `i` html tag (Font Awesome).

This is a callback for [PHP function `preg_replace_callback_array`](https://www.php.net/manual/en/function.preg-replace-callback-array).

#### repl(string $text)

Regex replacement of label and icon placeholders (uses the method `replcb`).

Example:

	$labels = ['hello' => 'Hello again'];
	...
	repls("@{hello} @icon{peace}")
		=> "<span class="command">Hello again</span> <i class="far fa-handshake"></i>"

#### repls(string $text)

Regex replacement of label and icon placeholders (uses the method `replcbs`).

Example:

	$labels = ['hello' => 'Hello again'];
	...
	repls("@{hello} @icon{peace}")
		=> "Hello again <i class="far fa-handshake"></i>"

#### link($label, $text)

Creates a URL to this web app using specified labels and/or text language.

If a parameter is an empty string, the current chosen (or default) language is used.

Example:

	link('', '')
		=> "index.php?ll=eng&tl=eng"
	link('ces', '')
		=> "index.php?ll=ces&tl=eng"
	link('', 'ces')
		=> "index.php?ll=eng&tl=ces"

#### kv2html($key, $val)

Converts a JSON object to an HTML code.

Example:

	JSON:
		{"a": "Hello"}
	Call:
		kv2html("a", "Hello"}
	Result:
		<div><span class="who">A:</span><span class="what">Hello</span></div>

#### html()

Return the order of mass in HTML based on the current chosen labels and text languages.