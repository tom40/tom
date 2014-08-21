<?php

class m121002_170505_add_report_criteria_data extends CDbMigration
{
	public function safeUp()
    {
        // (admin oneresult1234)
        $this->insert('report_criteria', array(
            'id'       => 1,
            'area'     => 'Receipt and submission of file',
            'score'    => 1,
            'criteria' => '<ul><li>Work must have been acknowledged by emailing transcripts.</li>
                            <li>File must have been returned on time, following all instructions given when it was assigned.</li>
                            <li>Assigning email must be included when file is returned to transcripts with email subject matching name of your file.</li>
                            <li>Checklist must have been ticked off upon a proofread before saving/sending your file.</li>
                            <li>Notetakers must send in the required table for live notes when returning each of their live files.</li>
                           </ul>',
        ));

        $this->insert('report_criteria', array(
            'id'       => 2,
            'area'     => 'Spelling/Spellcheck',
            'score'    => 1,
            'criteria' => '<ul><li>Spellings must be accurate. </li>
                            <li>File must have been spellchecked and all red lines omitted once you are sure spellings are correct.</li>
                           </ul>',
        ));

        $this->insert('report_criteria', array(
            'id'       => 3,
            'area'     => 'Accuracy',
            'score'    => 2,
            'criteria' => '<ul>
                            <li>Full files must be typed word for word.  Notes files and summaries must make sense and be clear and concise.</li>
                            <li>For the odd missed/misheard word, 1 point will be deducted.</li>
                            <li>For obvious accuracy issues that have taken time to correct, both points will be lost.</li>
                            <li>For inaccuracy on a higher level, overall score will be much lower and file will be sent back or retyped.</li>
                            </ul>',
        ));

        $this->insert('report_criteria', array(
            'id'       => 4,
            'area'     => 'Research',
            'score'    => 1,
            'criteria' => '<ul>
                            <li>All names/brands, etc. must be thoroughly researched and spelt correctly to receive this point.</li>
                            <li>Spellings must be correct and consistent throughout.</li></ul>',
        ));

        $this->insert('report_criteria', array(
            'id'       => 5,
            'area'     => 'Formatting',
            'score'    => 2,
            'criteria' => '<ul>
                            <li>Formatting should be fully in line with the guidelines.</li>
                            <li>For the odd error, 1 point will be deducted.</li>
                            <li>For more than this, both points will be lost.</li>
                            <li>Pay attention to template used, name of file (using Title Case if applicable), Transcriber Comments, ID of participants, font formatting, starter/ten-min timecodes, quotation marks/inverted commas, talking over each other, adverts, numbers, monetary amounts, times of day, etc.</li>
                           </ul>',
        ));

        $this->insert('report_criteria', array(
            'id'       => 6,
            'area'     => 'Punctuation',
            'score'    => 1,
            'criteria' => '<ul>
                            <li>Each sentence must be well-structured and adequately punctuated.</li>
                            <li>All sentences must end with the correct punctuation.</li>
                            <li>Commas and the -, format must be used correctly and unnecessary false starts omitted.</li>
                            <li>Maximum length of three lines for sentences and fifteen lines for paragraphs must have been adhered to where possible.</li>
                           </ul>',
        ));

        $this->insert('report_criteria', array(
            'id'       => 7,
            'area'     => 'Grammar',
            'score'    => 1,
            'criteria' => '<ul>
                            <li>Apostrophes used correctly throughout, to indicate possession and missing letters.</li>
                            <li>Ensure correct spellings are used with use of its/it’s, their/there/they’re, you’re/your, etc.</li>
                            <li>Grammatical errors made by the speaker should be corrected.</li>
                            <li>Avoid use of And/But at the start of sentences, where possible.</li>
                           </ul>',
        ));

        $this->insert('report_criteria', array(
            'id'       => 8,
            'area'     => 'Valid inaudibles/ph guesses',
            'score'    => 1,
            'criteria' => '<ul>
                            <li>Inaudible, missed word and phonetic guess formatting must be correct and noted at the correct times at which these occurred.</li>
                            <li>All of these must be valid and not easy to decipher when we listen to them.</li>
                           </ul>',
        ));
    }

    public function safeDown()
    {
        $this->delete('report_criteria', 'id = :id', array(':id' => 1));
        $this->delete('report_criteria', 'id = :id', array(':id' => 2));
        $this->delete('report_criteria', 'id = :id', array(':id' => 3));
        $this->delete('report_criteria', 'id = :id', array(':id' => 4));
        $this->delete('report_criteria', 'id = :id', array(':id' => 5));
        $this->delete('report_criteria', 'id = :id', array(':id' => 6));
        $this->delete('report_criteria', 'id = :id', array(':id' => 7));
        $this->delete('report_criteria', 'id = :id', array(':id' => 8));
    }
}