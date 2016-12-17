<?php

namespace FP\TimeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;

class HumanTimeType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $options['translation_domain'] = 'FPTimeBundle';
        $builder
                ->add('amount', 'number', [
                    'translation_domain' => $options['translation_domain'],
                    'label' => 'human_time_amount'
                ])
                ->add('size', 'choice', array(
                    // validation message if the data transformer fails
//                    'invalid_message' => 'That is not a valid issue number',
//                    'placeholder' => 'Choose',
                    'translation_domain' => $options['translation_domain'],
                    'label' => 'human_time_size',
                    'choices' => array(
                        'seconds' => 'size.choice_seconds',
                        'minutes' => 'size.choice_minutes',
                        'hours' => 'size.choice_hours',
                        'days' => 'size.choice_days',
                    )
        ));

        $builder->addModelTransformer(new CallbackTransformer(
                function ($time) {
            $amount = null;
            $size = 'seconds';
            $tokens = array(
                //31536000 => 'years',
                //2592000 => 'months',
                //604800 => 'weeks',
                86400 => 'days',
                3600 => 'hours',
                60 => 'minutes',
//                1 => 'seconds'
            );

            foreach ($tokens as $unit => $text) {
                if ($time < $unit)
                    continue;
                $amount = floor($time / $unit);
                $size = $text;
                break;
            }
            return array('amount' => $amount, 'size' => $size);
        }, function ($submitted) {
            switch ($submitted['size']) {
                case 'minutes':
                    $amount = $submitted['amount'] * 60;

                    break;

                case 'hours':
                    $amount = $submitted['amount'] * 3600;

                    break;

                case 'days':
                    $amount = $submitted['amount'] * 86400;

                    break;

                case 'seconds':
                default:
                    $amount = $submitted['amount'];
                    break;
            }
            return $amount;
        }
        ));
    }

    public function getName() {
        return 'human_time';
    }

}
