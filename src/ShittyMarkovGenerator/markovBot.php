<?php

namespace ShittyMarkovGenerator;

class markovBot
{

    protected $dictionary = [];
    protected $chainSize = [];

    public function __construct($text, $chainSize = 2)
    {
        $this->chainSize = $chainSize;
        $this->dictionary = $this->createDictionary($text, $this->chainSize);
    }

    private function createDictionary($text, $blockSize = 2)
    {
        $dictionary = null;

        $explode = preg_split('/[\s]+/', $text);

        /*
         * Main loop. This loop will construct the dictionary.
         * The structure is the following:
         * [key name composed by n words defined by blockSize][following word] = score
         */
        for ($i = 0; $i < (count($explode) - $blockSize); $i++) {
            // Making the key using n words
            $key = $explode[$i] . ' ' . $explode[$i + $blockSize - 1];

            // Selecting the following word
            $next = $explode[$i + $blockSize];

            $score = 1;
            // Checking if they $key => $next combo exists and assigning score
            if (isset($dictionary[$key][$next])) $score = $dictionary[$key][$next] + 1;

            $dictionary[$key][$next] = $score;
        }

        return $dictionary;
    }

    public function makeChain($chainSize = 10)
    {
        $lastBlock = array_rand($this->dictionary);
        $text = $lastBlock;

        for ($i = 0; $i < $chainSize; $i++) {
            $next = $this->findMatch($lastBlock);

            $text .= ' ' . $next;

            $lastBlock = $next;
        }

        return $text;
    }

    private function findMatch($lastBlock)
    {
        if (isset($this->dictionary[$lastBlock])) {
            $match = $this->dictionary[$lastBlock];
        } else {
            $match = $this->dictionary[array_rand($this->dictionary)];
        }
        $block = $this->selectNextBlock($match);

        return $block;
    }

    private function selectNextBlock($match)
    {
        $chances = $this->createChances($match);

        $random = mt_rand(1, $chances['total']);

        foreach ($chances['chances'] as $k => $i) {
            if ($random >= $i['bottom'] && $random <= $i['top']) return $k;
        }
    }

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

}
