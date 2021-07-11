<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Editing form for the OU multiple response question type class.
 *
 * @package    qtype_answersselect
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Editing form for the answersselect question type.
 *
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_answersselect_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        $mform->addElement('advcheckbox', 'shuffleanswers',
                get_string('shuffleanswers', 'qtype_multichoice'), null, null, array(0, 1));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_multichoice');
        $mform->setDefault('shuffleanswers', 1);
        
        $mform->addElement('select', 'answernumbering',
                get_string('answernumbering', 'qtype_multichoice'),
                qtype_multichoice::get_numbering_styles());
        $mform->setDefault('answernumbering', 'abc');

        $mform->addElement('selectyesno', 'showstandardinstruction',
            get_string('showstandardinstruction', 'qtype_answersselect'), null, null, [0, 1]);
        $mform->addHelpButton('showstandardinstruction', 'showstandardinstruction', 'qtype_answersselect');
        $mform->setDefault('showstandardinstruction', 0);
        
        $menu = array('Use all the answers', 'Manual selection',
            'Automatic random selection');
        $mform->addElement('select', 'answersselectmode', 'Number of correct and incorrect answers', $menu);
        
        
        // DEV JR JULY 2021        
        // Check if we are starting a new question.
        if (isset($this->question->options->answers)) {
            $currentanswers = $this->question->options->answers;
            $answercount = count($currentanswers);
            $numberofcorrectanswers = 0;
            foreach ($currentanswers as $answer) {
                if ($answer->fraction == 1) {
                    $numberofcorrectanswers++;
                }
            }
            $correctoptions = range($numberofcorrectanswers, 1);            
            $mform->addElement('select', 'randomselectcorrect',
                'Number of selected correct answers',
                $correctoptions                                                
            );
            $mform->setDefault('randomselectcorrect', 0);
            $mform->hideIf('randomselectcorrect', 'answersselectmode', 'neq', 1);
            
            $incorrectoptions = range($answercount - $numberofcorrectanswers, 1);; 
            $mform->addElement('select', 'randomselectincorrect',
                'Number of selected incorrect answers',
                $incorrectoptions                
            );
            $mform->setDefault('randomselectincorrect', 0);
            $mform->hideIf('randomselectincorrect', 'answersselectmode', 'neq', 1);
        };   
        // END DEV JR
        $this->add_per_answer_fields($mform, get_string('choiceno', 'qtype_multichoice', '{no}'),
                null, max(5, QUESTION_NUMANS_START));

        $this->add_combined_feedback_fields(true);

        $this->add_interactive_settings(true, true);
    }

    protected function get_per_answer_fields($mform, $label, $gradeoptions,
            &$repeatedoptions, &$answersoption) {
        $repeated = array();
        $repeated[] = $mform->createElement('editor', 'answer',
                $label, array('rows' => 1), $this->editoroptions);
        $repeated[] = $mform->createElement('checkbox', 'correctanswer',
                get_string('correctanswer', 'qtype_answersselect'));
        $repeated[] = $mform->createElement('editor', 'feedback',
                get_string('feedback', 'question'), array('rows' => 1), $this->editoroptions);

        // These are returned by arguments passed by reference.
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $answersoption = 'answers';

        return $repeated;
    }

    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false) {
        list($repeated, $repeatedoptions) = parent::get_hint_fields(
                $withclearwrong, $withshownumpartscorrect);

        // Add the new option the the last group in repeat if there is one, otherwise
        // as a new element.
        $lastgroup = null;
        foreach ($repeated as $element) {
            if ($element->getType() == 'group') {
                $lastgroup = $element;
            }
        }

        $showchoicefeedback = $this->_form->createElement('advcheckbox', 'hintshowchoicefeedback', '',
                get_string('showeachanswerfeedback', 'qtype_answersselect'));
        if ($lastgroup) {
            $lastgroup->_elements[] = $showchoicefeedback;
        } else {
            $repeated[] = $showchoicefeedback;
        }

        return array($repeated, $repeatedoptions);
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question, true);
        $question = $this->data_preprocessing_combined_feedback($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);

        if (!empty($question->options->answers)) {
            $key = 0;
            foreach ($question->options->answers as $answer) {
                $question->correctanswer[$key] = $answer->fraction > 0;
                $key++;
            }
        }

        if (!empty($question->hints)) {
            $key = 0;
            foreach ($question->hints as $hint) {
                $question->hintshowchoicefeedback[$key] = !empty($hint->options);
                $key += 1;
            }
        }

        if (!empty($question->options)) {
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->answernumbering = $question->options->answernumbering;
            $question->showstandardinstruction = $question->options->showstandardinstruction;
            $question->answersselectmode = $question->options->answersselectmode;
            $question->randomselectcorrect = $question->options->randomselectcorrect;
            $question->randomselectincorrect = $question->options->randomselectincorrect;
        }
        
        return $question;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $answers = $data['answer'];
        $answercount = 0;
        $numberofcorrectanswers = 0;
        foreach ($answers as $key => $answer) {
            $trimmedanswer = trim($answer['text']);
            if (empty($trimmedanswer)) {
                continue;
            }

            $answercount++;
            if (!empty($data['correctanswer'][$key])) {
                $numberofcorrectanswers++;
            }
        }

        // Perform sanity checks on number of correct answers.
        if ($numberofcorrectanswers == 0) {
            $errors['answer[0]'] = get_string('notenoughcorrectanswers', 'qtype_answersselect');
        }

        // Perform sanity checks on number of answers.
        if ($answercount == 0) {
            $errors['answer[0]'] = get_string('notenoughanswers', 'qtype_multichoice', 2);
            $errors['answer[1]'] = get_string('notenoughanswers', 'qtype_multichoice', 2);
        } else if ($answercount == 1) {
            $errors['answer[1]'] = get_string('notenoughanswers', 'qtype_multichoice', 2);
        }

        return $errors;
    }

    public function qtype() {
        return 'answersselect';
    }
}
