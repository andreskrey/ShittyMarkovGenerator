<?php

namespace ShittyMarkovGenerator;

class markovBot
{
    /** @var string Dictionary to create chains */
    protected $dictionary = [];

    /** @var int Number of words to use as chain link */
    protected $chainSize = [];

    /**
     * Main __construct.
     *
     * Construct the dictionary and set the chain size
     *
     * @param string $text
     * @param int $chainSize
     */
    public function __construct($text, $chainSize = 2)
    {
        $this->chainSize = $chainSize;
        $this->dictionary = $this->createDictionary($text, $this->chainSize);
    }


    /**
     * Dictionary is constructed using the blockSize.
     * It consists of an array with all the input text exploded by its spaces.
     *
     * @param string $text
     * @param int $blockSize
     * @return array
     */
    private function createDictionary($text, $blockSize = 2)
    {
        $dictionary = null;

        $explode = preg_split("/\s+/", $text);

        /*
         * Main loop. This loop will construct the dictionary.
         * The structure is the following:
         * [key name composed by n words defined by blockSize][following word] = score
         */
        for ($i = 0; $i < (count($explode) - $blockSize); $i++) {
            // Making the key using n words
            $key = implode(' ', array_slice($explode, $i, $blockSize));

            // Selecting the following word
            $next = $explode[$i + $blockSize];

            $score = 1;
            // Checking if they $key => $next combo exists and assigning score
            if (isset($dictionary[$key][$next])) $score = $dictionary[$key][$next] + 1;

            $dictionary[$key][$next] = $score;
        }

        return $dictionary;
    }

    /*
     * Main entry point. This will generate the final Markov chain.
     *
     * @param int $chainSize
     * @param string $theme
     */
    public function makeChain($chainSize = 10, $theme = null)
    {
        /*
         * The chain can have a theme. If it set, the starting point of the chain will be the topic.
         * Otherwise a random chain will be selected
         */
        if ($theme) {
            $lastBlock = $this->getTheme($theme);
        } else {
            $lastBlock = array_rand($this->dictionary);
        }

        $text = $lastBlock;

        /*
         * Main for loop. This will create the chain requesting new blocks based on the previous one.
         */
        for ($i = 0; $i < $chainSize; $i++) {
            /*
             * Since the sentences should end in punctuation marks (!?. etc), when the last loop is reached
             * a flag accordingly must be set.
             */
            $endSentence = false;
            if ($i == $chainSize - 1) $endSentence = true;
            $next = $this->findMatch($lastBlock, $endSentence);

            $text .= ' ' . $next;

            $lastBlock = explode(' ', $lastBlock)[1] . ' ' . $next;
        }

        return $text;
    }

    /*
     * This function will try to find a match to the previous chain.
     *
     * @param var $lastBlock
     * @param bool $endSentence
     */
    private function findMatch($lastBlock, $endSentence = false)
    {
        /*
         * If this is the last sentence, the function will try to search for a block that ends on a
         * punctuation mark, trying to match it with the previous block. If this is not possible, a random
         * block will be selected.
         */
        if ($endSentence) {
            $explode = explode(' ', $lastBlock);
            $lastWord = end($explode);

            // Select all the blocks ending on punctuation mark, that contains the last word of the block.
            $endings = preg_grep("/" . $lastWord . " .+([.!?]$)/", array_keys($this->dictionary));

            if ($endings) {
                return substr($endings[array_rand($endings)], strlen($lastWord) + 1);
            } else {
                // No luck, select a random block.
                $match = $this->dictionary[array_rand($this->dictionary)];
            }
        } else {
            // Trying to match the current block.
            if (isset($this->dictionary[$lastBlock])) {
                $match = $this->dictionary[$lastBlock];
            } else {
                $match = $this->dictionary[array_rand($this->dictionary)];
            }
        }

        $block = $this->selectNextBlock($match);

        // Deleting the selected block in order to avoid repeating it in the next loop
        unset($this->dictionary[$lastBlock][$block]);

        return $block;
    }

    /*
     * This function will find the next block, based on the chances it has to appear next to the previous block.
     *
     * @param array $match
     * @param bool $endSentence
     */
    private function selectNextBlock($match)
    {
        // Of course it makes sense to create chances and select it if we have more than one option.
        if (count($match) > 1) {
            $chances = $this->createChances($match);
        } else {
            return array_keys($match)[0];
        }

        // Select the winning block
        $random = mt_rand(1, $chances['total']);

        // Scan the chances and extract the winning block.
        foreach ($chances['chances'] as $k => $i) {
            if ($random >= $i['bottom'] && $random <= $i['top']) return $k;
        }
    }

    /*
     * The idea behind this function is to select the most appropriate word, based on the chances it has to appear
     * next to the selected word. If one word appears more times next to another, this word should be selected more times
     * instead of selecting a random one of the group.
     *
     * This function will create an array of all the options, with the amount of chances each one has.
     *
     * @param array $options
     * @param bool $endSentence
     * $return array
     */
    private function createChances($options)
    {
        $bottom = 1;
        $chances = [];

        foreach ($options as $k => $i) {
            $chances[$k] = [
                'bottom' => $bottom,
                'top' => $bottom + $i - 1
            ];

            $bottom = $bottom + $i;
        }

        return [
            'chances' => $chances,
            'total' => $bottom - 1
        ];
    }

    /*
     * Select a theme by searching it on the dictionary and use it as a starting point.
     *
     * @param string $theme
     * $return array
     */
    private function getTheme($theme)
    {
        $search = preg_grep('/\b' . $theme . '/', array_keys($this->dictionary));

        if (!$search) return $this->dictionary[array_rand($this->dictionary)];

        return $search[array_rand($search)];
    }
}
