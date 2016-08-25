# ShittyMarkovGenerator
## A shitty implementation of the Markov chains

### What?

If you take a text, explode it, and then inspect the words, you'll see that some words appear more often next to some 
other words. Sometimes words appear next to each other rarely. If you explore that relations and assign a score to each
relation, you can use this information to create phrases that will have a correct structure and, hopefully, some meaning. This exploration task
is, in a super simplified way, Markov chains.

This project will create phrases based on the texts that you feed it. In a way, the bigger the text, the "smarter" the 
phrase it will generate.

### How?

When you feed a text to the Chainer function, it will explode all the words and create a dictionary, assigning a
specific score to each word and the next one, based on repetition.

Then it will output a phrase based on that dictionary. You can select the chain size (lenght of the phrase), the topic
(it will try to match the topic you define with a word in the dictionary and start the phrase from there) and the block size
(the way the dictionary will be constructed). 

### Usage

You can use it either by CLI or as a dependency. Download the project and use `composer install` to generate the 
autoloader.

Otherwise just run it by CLI.

`php chainer.php -r /path/to/your/text`

The example text can be anything. Just make sure it's long enough to create interesting phrases.

### Why is it better than other Markov generators out there?

What I like about my project is that it doesn't select words randomly. The other projects I saw, selected the word from
the pool with a random function. In this project, words are selected by chances. When the dictionary is constructed, a score
is assigned to the following possible word. At the moment of selecting a new word, if there is more than one option,
those options will be selected by random, but those words that naturally have more chances to appear next to the
selected one will be selected more often. Check the `createChances` function on the markovBot file to understand it better.

It also tries to end the phrase in a punctuation mark. This doesn't work perfectly, but given the right conditions, it
will give you a phrase that ends in `!`, `.` or `?`.

### Bugs

- Block size is tricky right now. If you select a bigger block size than 2, it will repeat itself when generating 
phrases. When a word is selected, the previous block is deleted from the dictionary to avoid repetition, but because
of the way the dictionary is generated, similar combinations are still present on it.

### Acknowledgements

This project is heavily based on the [markov-php](https://github.com/heidilabs/markov-php/) by [heidilabs](https://github.com/heidilabs).