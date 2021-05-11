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
is not JSON nor XML,
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

stay tuned...