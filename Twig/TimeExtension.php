<?php

namespace FP\TimeBundle\Twig;

//use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class TimeExtension extends \Twig_Extension
{
    protected $translator;

    /**
     * Constructor
     *
     * @param  TranslatorInterface $translator Translator used for messages
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('romanic_number', array($this, 'romanicNumberFilter')),
            new \Twig_SimpleFilter('human_time', array($this, 'humanTimeFilter')),
        );
    }

    function romanicNumberFilter($integer, $upcase = true)
    {
        $table = array(
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1,
        );
        $return = '';
        while ($integer > 0) {
            foreach ($table as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $return .= $rom;
                    break;
                }
            }
        }

        return $return;
    }

    public function humanTimeFilter($time, $args = [], $translation_domain = 'FPTimeBundle')
    {
        if ($time instanceof \DateTime) {
            $time = $time->getTimestamp();
            if (array_key_exists('type', $args)) {
                switch ($args['type']) {
                    case 'ms':
                    case 'mili':
                    case 'miliseconds':
                        $time /= 1000;
                        break;

                    case 'i':
                    case 'm':
                    case 'min':
                    case 'minute':
                    case 'minuts':
                        $time *= 60;
                        break;

                    case 'h':
                    case 'hour':
                    case 'hours':
                        $time *= 3600;
                        break;

                    case 's':
                    case 'sec':
                    case 'second':
                    case 'seconds':
                    default:
                        break;
                }
            }
        }


        $precision = (array_key_exists('precision', $args) && $args['precision']) ? (int)$args['precision'] : 2;


        if (array_key_exists('ago', $args) && $args['ago']) {
            $now = time();
            if ($now < $time) {
                return 'in_the_future';
            }
            $time = $now - $time; // to get the time since that moment
        }


        $time = ($time < 1) ? 1 : $time;
        $tokens = array(
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second',
        );


        $out = $coma = '';
        $i = 0;
        foreach ($tokens as $amount => $unit) {
            if ($i < $precision) {
                if ($time < $amount) {
                    unset($tokens[$amount]);
                    continue;
                }
                $count = floor($time / $amount);
                $time = $time - $amount * $count;
                if ($count == 1 && $i > 0) {
                    $out = $out.' 1 ';
                }
                $out .= $coma.$this->translator->transChoice(
                        sprintf('size.%s', $unit),
                        $count,
                        array('%count%' => $count),
                        $translation_domain
                    );
                $i++;
                $coma = ' ';
            } else {
                break;
            }
        }

        return $out;
    }


//        if ($seconds < 60) {
//            return $seconds.' sek';
//        } elseif ($seconds < 3600) {
//            return sprintf("%d min %d sek", ($seconds / 60) % 60, $seconds % 60);
//        } elseif ($seconds < 86400) {
//            return sprintf("%d godz %d min", ($seconds / 60 / 60) % 24, ($seconds / 60) % 60);
//        } else {
//            return sprintf("%d dni %d godz", $seconds / 60 / 60 / 24, ($seconds / 60 / 60) % 24);
//        }


    public function getName()
    {
        return 'fp_timetools';
    }

}

