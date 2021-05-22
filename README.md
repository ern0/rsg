# RSG - Random Sentence Generator


## Foreword


### Mission

Random text generation is fun.
I've created this engine 
for committed funmakers,
including myself.

The engine is written in PHP, 
and supports the use of HTML.
The database script language
is not JSON, nor XML,
but plain text, similar to
Makefile.

It was a design guideline that
the engine should be suitable for
Hungarian language, because 
it's so complicated, that
other languages will also work then.


### You are the author

If you want to write a good program,
a good compiler is only a tool.

Also, this RSG engine is only a tool.
Good quality result requires 
**lot** of quality content.

RSG is not an easy genre.


## Integrator's manual


### Add to your program

Load text, then pass parsed nodes to the renderer:

	$text = new Text("rsg_kontent.txt");
	$text->load();
	$renderer = new Renderer(null, $text->nodes);
	$renderer->render("@main");

If you want to print the result later,
you can change the last line to these:

	$renderer->isDirectRendering = false;
	$result = $renderer->render("@main");


### Tests

If you want to understand 
how the program works,
you should first study the tests.
The coverage is not 100%,
but there're tests for neuralgic points.

Also, tests can be useful when you
modify the program, and want to see
whether something broke.

You can run tests by starting the 
script named `t`, which calls `test/test.php`.
The result should be like:

	[okay] - RsgSuite - cases: 13, passed: 13, failed: 0, asserts: 146, passed: 146, failed: 0


### Web server requirements

I've tested it with Apache2.

The program requires PHP7,
with `mbstring` module installed.
Probably it's preinstalled, if not,
to install it on a Debian system,
run this command:

	sudo apt install php-mbstring


### Dev server

You might start a PHP dev server
for development,
including RSG script writing, 
by starting the script named `a`, 
which simply runs `php -S localhost:8080`.

Then you can point your browser to
`localhost:8080`, 
and select RSG script from the menu.


## RSG author's manual

This chapter contains tutorials,
each introduces some features,
from easy to difficult ones.
You will able to write funny scripts
after the first lession, but
it's worth to go on and 
use advanced features.

You can found all the examples in the repository,
and also you can try them at:
http://linkbroker.hu/stuff/rsg/


### Tutor-1: Nodes and tags

Let's jump into the script:

	node id=main
		My favorite color is #color

	node tag=color
		black
	node tag=color
		blue
	node tag=color
		gray
	node tag=shape
		circle

All the text data is organized into nodes.
A node may have properties, like *id* or *tag*.
Node definitions are starting at the first column,
lines starting with any whitespace (space, tab) 
belongs to the node.

When the engine starts,
it picks the *main* node, 
which's *id* property is *main*,
and renders it.

The engine renders words and HTML tags
without any change.
When the engine reach a
*reference*,
it selects a node 
from the set specified by the reference,
then renders the node instead of the reference.

The most simple form of a reference is
the tag selector:
a hashmark followed by a tag value.
The engine will filter for all nodes,
which has *tag* property with the specified value,
and will randomly select one of them.

Let's run it several times:

	• My favorite color is gray
	• My favorite color is blue
	• My favorite color is black
	• My favorite color is blue
	• My favorite color is black
	• My favorite color is gray
	• My favorite color is gray
	• My favorite color is black

Note, that *circle* will be not selected,
because its tag is *shape*.


### Tutor-2: Unique selections

Once a node is selected,
it will be not selected any more.

	node id=main
		Do you now the song #color and #color?

	node tag=color
		Black
	node tag=color
		Blue
	node tag=color
		Red
	node tag=color
		Green
	node tag=color
		Yellow
	node tag=color
		Purple
	node tag=color
		White

Run this script more times:

	• Do you now the song Purple and White?
	• Do you now the song Purple and Green?
	• Do you now the song Green and White?
	• Do you now the song White and Purple?
	• Do you now the song Black and Purple?
	• Do you now the song Blue and Purple?
	• Do you now the song Red and Black?
	• Do you now the song Yellow and White?

As you can see, 
there are only different colors in song titles.


### Tutor-3: Going deeper

This is the feature,
which overshines simple
random sentence generators,
which simply 
selects three items 
from three set of words.

Generate a funny animal,
describing its size, color and species,
but let's tweak it:
sometimes don't tell its size or color,
and sometimes add shade for the color!

	node id=main
		Kitty is a #kind. I love Kitty.
			
	node tag=kind
		#size #shade #color #species
	node tag=kind
		#size #color #species
	node tag=kind
		#shade #color #species
	node tag=kind
		#color #species
	node tag=kind
		#size #species

	node tag=size
		tiny
	node tag=size
		small
	node tag=size
		big
	node tag=size
		huge

	node tag=shade
		light
	node tag=shade
		dark

	node tag=color
		blue
	node tag=color
		red
	node tag=color
		green
	node tag=color
		yellow
	node tag=color
		purple
	node tag=color
		orange
	node tag=color
		turquoise

	node tag=species
		dog
	node tag=species
		cat
	node tag=species
		elephant
	node tag=species
		snail

The main node selects
which properties of the animal will be specified,
the *kind* nodes are the availalbe variations.
The result is:

	• Kitty is a huge light purple cat.
	• Kitty is a light red cat.
	• Kitty is a red cat.
	• Kitty is a blue dog.
	• Kitty is a huge elephant.
	• Kitty is a dark orange cat.
	• Kitty is a huge turquoise cat.
	• Kitty is a light blue dog.


### Tutor-4: Random errors

The bad news is that 
errors may pop up randomly,
according to actual selections.
The good new is that
there are only one error type,
which can happen for two reasons.

	node id=main
		Error #blahblah #maybe

	node tag=maybe
		#not_enough
	node tag=maybe
		#not_enough #not_enough
		
	node tag=not_enough
		black

If you make a typo in a reference,
or just forget to create a set for it,
the error is guaranteed.
This is the better case, anyway.

The worst case is, 
when you have a set,
which might be referenced more times
than the number of elements,
but not always.
Be careful!

	• Error ERROR(tag=blahblah) black ERROR(tag=not_enough)
	• Error ERROR(tag=blahblah) black

The engine will render an error message with the
parameters of the failed reference.


### Tutor-5: Short syntax for text property

Whatever you write under a node,
starting with a whitespace,
will be added to the node's 
*text* property.
If you have really short text,
which does not contain spaces,
you can define it as other properties.

	node id=main
		My favourite color is #color.

	node tag=color text=black
	node tag=color text=white
	node tag=color text=blue
	node tag=color text=red
	node tag=color text=green
	node tag=color text=yellow

Some results:

	• My favourite color is green.
	• My favourite color is white.
	• My favourite color is green.
	• My favourite color is blue.
	• My favourite color is blue.
	• My favourite color is blue.
	• My favourite color is red.
	• My favourite color is green.


### Tutor-6: Rendering custom property

By default, the node's `text` property is rendered.
But this can be overwritten,
appending a dot and the name of the property
to the reference.

	node id=main
		My favourite color is #color.x.

	node tag=color x=black
		text property, not in use
	node tag=color x=white
	node tag=color x=blue
	node tag=color x=red
	node tag=color x=green
	node tag=color x=yellow

Some results:

	• My favourite color is white.
	• My favourite color is green.
	• My favourite color is black.
	• My favourite color is red.
	• My favourite color is green.
	• My favourite color is blue.
	• My favourite color is white.
	• My favourite color is black.

Note, that the `text` property
is not used for black.


### Tutor-7: Remember and recall

Using variables makes the generated text 
more natural.
Combining them with different properties
is a killer feature.

	node id=main
		@who=#name is a good @who.sex.

	node tag=name text=Maria sex=girl
	node tag=name text=Jon sex=boy
	node tag=name text=Robert sex=boy
	node tag=name text=Lisa sex=girl

Some results are:

	• Robert is a good boy.
	• Maria is a good girl.
	• Jon is a good boy.
	• Maria is a good girl.
	• Lisa is a good girl.
	• Jon is a good boy.
	• Jon is a good boy.
	• Maria is a good girl.

Variables once set can be recalled 
any time,
any place, 
and you can use 
any properties of the recalled node.

Properties make possible to use
conjugated forms of the main word
(useful for Hungarian and Slavic languages), 
related pronouns or articles for it
(useful for German and Slavic languages),
or even just set up synonims, 
or any related stuff.

> *This comment is not important for the users,
but if you browse the program code,
probably you will meet this issue.
A variable contains not the rendered text,
but the reference of the node selected.
When the variable is used,
it will be rendered in a special fashion:
all its references will choose the very same node
which were selected first time.*

If the referenced variable is not set,
the engine will select the node,
which has `id` property with the specified value.
It can be used as fallback 
if a variable setter is uncertainly selected,
or if you just want to use a shortcut for a
frequently used text.
Also, from the user program, 
the engine should be called with a root reference,
which is `"@main"` by default.


### Tutor-8: Advanced search

You can specify custom tags for search,
even using variables as search values.

For a node, multiple values can be defined
for the same property (see Android),
or you can use asterisk 
for getting potentially selected for 
any search value for the property
(see Javascript).

	node id=main

		For 
		@purp=#purpose.long, 
		@lang=[tag=language,purpose=@purp.token]
		is a bad choice.
		[tag=language,purpose=@purp.token]
		is a better
		@purp.short language.

	node tag=purpose token=backend
		long= backend purposes
		short= backend
	node tag=purpose token=frontend
		long= frontend purposes
		short= frontend
	node tag=purpose token=android
		long= Android development
		short= Android
	node tag=purpose token=windows
		long= Windows development
		short= Windows

	node tag=language purpose=windows
		Visual Basic
	node tag=language purpose=windows
		C#
	node tag=language purpose=backend purpose=android
		Java
	node tag=language purpose=backend
		Python
	node tag=language purpose=android
		Kotlin
	node tag=language purpose=backend
		PHP
	node tag=language purpose=frontend
		TypeScript
	node tag=language purpose=*
		Javascript

Some results:

	• For frontend purposes, TypeScript is a bad choice. Javascript is a better frontend language.
	• For Windows development, Visual Basic is a bad choice. Javascript is a better Windows language.
	• For frontend purposes, TypeScript is a bad choice. Javascript is a better frontend language.
	• For Android development, Kotlin is a bad choice. Java is a better Android language.
	• For backend purposes, Java is a bad choice. Javascript is a better backend language.
	• For frontend purposes, TypeScript is a bad choice. Javascript is a better frontend language.
	• For frontend purposes, TypeScript is a bad choice. Javascript is a better frontend language.
	• For Android development, Java is a bad choice. Javascript is a better Android language.


### Tutor-9: Decorators

There are three decorators,
they alter only the rendering.

* Hide ("h"): the node will be 
not rendered at all. 
It's a good practice to set variables
silently at the beginning of the script.
* Capitalize ("c"): capitlizes the first letter
of the word. 
It's useful for sentence beginnings.
* Skip first word ("s"): omits rendering of
first word of the node.
Designed to hide the article,
if it's not needed.
It's very useful 
for languages with more form
of article (German, Slavic languages).

	node id=main
		@who=#who!h 
		@what=#what!h

		@who.en!sc + @what.e1: 
		@who.en!c @what.e2.
		-
		@who.de!sc + @what.d1: 
		@who.de!c @what.d2.
		
	node tag=who
		en= the boy
		de= Der Junge
	node tag=who
		en= the girl
		de= Das Mädchen
	node tag=who
		en= the woman
		de= Die Frau
	node tag=who
		en= the man
		de= Der Mann

	node tag=what
		e1= stand
		e2= is standing
		d1= Stand
		d2= steht
	node tag=what
		e1= sit
		e2= is sitting
		d1= sitzen
		d2= sitzt
	node tag=what
		e1= lay
		e2= lies
		d1= liegen
		d2= lügt

There're some results:

	• Girl + lay: The girl lies. -  Mädchen + liegen: Das Mädchen lügt.
	• Boy + sit: The boy is sitting. -  Junge + sitzen: Der Junge sitzt.
	• Boy + lay: The boy lies. -  Junge + liegen: Der Junge lügt.
	• Man + sit: The man is sitting. -  Mann + sitzen: Der Mann sitzt.
	• Woman + lay: The woman lies. -  Frau + liegen: Die Frau lügt.
	• Girl + stand: The girl is standing. -  Mädchen + Stand: Das Mädchen steht.
	• Girl + stand: The girl is standing. -  Mädchen + Stand: Das Mädchen steht.
	• Girl + lay: The girl lies. -  Mädchen + liegen: Das Mädchen lügt.


### Tutor-10: Weight

By default, every node has a `weight` property
with value of 100. 
You can define weight value for any node,
so you can influence the choice.
It a weight is zero, it will be never selected.

In a reference,
you can also choose,
which tag is used for weighting.
If a node of the result set
has no such property, 
it will be counted as 100.

	node id=main

		#place

	node tag=place weight=40
		I am a #sex
		living in Europe,
		I have [tag=phone,%eu]
		and
		[tag=car,%eu]
	node tag=place weight=50
		I am a #sex
		living in USA,
		I have [tag=phone,%usa]
		and
		[tag=car,%usa]
	node tag=place weight=20
		I am a #sex
		living in Dubai,
		I have [tag=phone,%dubai]
		and
		[tag=car,%dubai]

	node tag=sex weight=49 text=man
	node tag=sex weight=51 text=woman

	node tag=phone
		iPhone
		eu= 20
		usa= 50
		dubai= 95
	node tag=phone
		Android smartphone
		eu= 80
		usa= 50
		dubai= 5

	node tag=car
		Opel
		usa= 0
		dubai= 0
	node tag=car
		Ford
		dubai= 1
	node tag=car
		Chrysler
		eu= 20
		dubai= 1
	node tag=car
		Chevrolet
		usa= 150
		dubai= 1
	node tag=car
		Mercedes
		usa= 30
		dubai= 5
	node tag=car
		Tesla
		eu= 10
		usa= 10
	node tag=car
		Fiat
		dubai= 0
		usa= 0
	node tag=car
		Ferrari
		usa= 1
		eu= 2
	node tag=car
		Lamborghini
		usa= 1
		eu= 2

There're some results:

	• I am a man living in USA, I have iPhone and Chevrolet
	• I am a man living in Europe, I have Android smartphone and Fiat
	• I am a man living in USA, I have Android smartphone and Ford
	• I am a woman living in Dubai, I have iPhone and Tesla
	• I am a woman living in Dubai, I have iPhone and Lamborghini
	• I am a man living in Europe, I have iPhone and Opel
	• I am a woman living in Europe, I have Android smartphone and Mercedes
	• I am a woman living in Europe, I have Android smartphone and Chevrolet

### Include, comment

Any special character at first column 
can be used as comment.

Include commands should be anywhere in the script.
It's a good idea to split up big projects to 
more files, or build general-purpose includes.

	---- include example ---------
	node id=main
		Look, mom, a #color #shape!

	include shape.inc
	include color.inc
	------------------------------

Some results:

	• Look, mom, a green paralellogram!
	• Look, mom, a yellow rectangle!
	• Look, mom, a purple square!
	• Look, mom, a white paralellogram!
	• Look, mom, a black triangle!
	• Look, mom, a green paralellogram!
	• Look, mom, a cyan rectangle!
	• Look, mom, a gray rhombus!

### Summary

* The script consist of nodes, nodes have properties with key and value
* The engine starts at the node with `id=main`
* The engine renders the `text` property by default
* Node syntax is: `node key=value`
* The text property can be defined as lines starting with whitespace
* Longer properties can be defined such, but first word must be the name of the property, e.g. `key=`, then a space followed by value
* When the rendering engine is reaching a reference, it selects a node, and renders it
* Full syntax of reference: @var=[key1=value1,key2=value2,%weightkey].prop!hsc
* Short syntax: @id.prop!hsc or #tag.prop.hsc
* Decorators are: Hide, Skip-first-word and Capitalize 


## Politics


### Known issues

Nothing serious, probably all issues fall to 
the "won't fix" category.

* In the full reference, 
only constant weight keys are allowed,
variables should be used
* Circular references are not handled, 
PHP will die with stack overflow error
* Line numbers should be added to error messages
* The program is designed to render the result on-the-fly, so no post-processing applied on the result text. This led to the minor bug that the renderer sometimes adds extra spaces between words at references. Did not fixed, it's not important for HTML
* References should be surrounded by spaces,
with some exception, e.g. end-of-sentence punctation works
* The program is originally designed to be easily rewritten to C or C++, e.g. render on-the-fly, easy-to-parse format etc. I gave up this slowly, at last at the point, when I recognized that variables should not only contain a reference to a node, but all subsequent choices should be preserved in order to render the same text when the node is re-selected by a variable.


### Project future


A C or C++ implementation for embedded devices
(e.g. no memory allocation etc.) would be cool.