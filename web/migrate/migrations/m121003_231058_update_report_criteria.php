<?php

class m121003_231058_update_report_criteria extends CDbMigration
{
	public function safeUp()
    {
        $this->update('report_criteria', array(
            'criteria' => '<ul><li>Work must have been <span class="highlight">acknowledged</span> by emailing transcripts.</li>
                            <li>File must have been <span class="highlight">returned on time, following all instructions given</span> when it was assigned.</li>
                            <li><span class="highlight">Assigning email must be included</span> when file is returned to <span class="highlight">transcripts</span> with email subject matching name of your file.</li>
                            <li><span class="highlight">Checklist must have been ticked off</span> upon a proofread before saving/sending your file.</li>
                            <li><span class="highlight">Notetakers must send in the required table for live notes</span> when returning each of their live files.</li>
                           </ul>'
            ),
            'id = :id',
             array('id' => 1)
        );

        $this->update('report_criteria', array(
            'criteria' => '<ul><li><span class="highlight">Spellings must be accurate</span>. </li>
                            <li>File must have been <span class="highlight">spellchecked</span> and all <span class="highlight">red lines omitted</span> once you are sure spellings are correct.</li>
                           </ul>',
            ),
            'id = :id',
             array('id' => 2)
        );

        $this->update('report_criteria', array(
            'criteria' => '<ul>
                            <li>Full files must be typed <span class="highlight">word for word</span>.  Notes files and summaries must make sense and be clear and concise.</li>
                            <li>For the odd missed/misheard word, 1 point will be deducted.</li>
                            <li>For obvious accuracy issues that have taken time to correct, both points will be lost.</li>
                            <li>For inaccuracy on a higher level, overall score will be much lower and file will be sent back or retyped.</li>
                            </ul>',
            ),
            'id = :id',
             array('id' => 3)
        );

        $this->update('report_criteria', array(
            'criteria' => '<ul>
                            <li><span class="highlight">All</span> names/brands, etc. must be thoroughly researched and spelt correctly to receive this point.</li>
                            <li>Spellings must be <span class="highlight">correct and consistent</span> throughout.</li></ul>',
            ),
            'id = :id',
             array('id' => 4)
        );

        $this->update('report_criteria', array(
            'criteria' => '<ul>
                            <li>Formatting should be <span class="highlight">fully in line with the guidelines</span>.</li>
                            <li>For the odd error, 1 point will be deducted.</li>
                            <li>For more than this, both points will be lost.</li>
                            <li>Pay attention to <span class="highlight">template</span> used, <span class="highlight">name of file</span> (using Title Case if applicable), <span class="highlight">Transcriber Comments, ID of participants, font formatting, starter/ten-min timecodes, quotation marks/inverted commas, talking over each other, adverts, numbers, monetary amounts, times of day, </span>etc.</li>
                           </ul>',
            ),
            'id = :id',
             array('id' => 5)
        );

        $this->update('report_criteria', array(
            'criteria' => '<ul>
                            <li>Each sentence must be <span class="highlight">well-structured</span> and <span class="highlight">adequately punctuated.</span></li>
                            <li>All sentences must <span class="highlight">end with the correct punctuation.</span></li>
                            <li>Commas and the -, format must be used correctly and <span class="highlight">unnecessary false starts omitted.</li>
                            <li><span class="highlight">Maximum length of three lines for sentences</span> and <span class="highlight">fifteen lines for paragraphs</span> must have been adhered to where possible.</li>
                           </ul>',
            ),
            'id = :id',
             array('id' => 6)
        );

        $this->update('report_criteria', array(
            'criteria' => "<ul>
                            <li>Apostrophes used correctly throughout, to <span class='highlight'>indicate possession and missing letters.</span></li>
                            <li>Ensure correct spellings are used with use of its/it's, their/there/they're, you're/your, etc.</li>
                            <li><span class='highlight'>Grammatical errors made by the speaker</span> should be corrected.</li>
                            <li>Avoid use of <span class='highlight'>And/But at the start of sentences</span>, where possible.</li>
                           </ul>",
            ),
            'id = :id',
             array('id' => 7)
        );

        $this->update('report_criteria', array(
            'criteria' => '<ul>
                            <li>Inaudible, missed word and phonetic guess <span class="highlight">formatting must be correct</span> and <span class="highlight">noted at the correct times</span> at which these occurred.</li>
                            <li>All of these <span class="highlight">must be valid and not easy to decipher</span> when we listen to them.</li>
                           </ul>',
            ),
            'id = :id',
             array('id' => 8)
        );

    }

    public function safeDown()
    {
        $this->update('report_criteria', array(
            'criteria' => '<ul><li>Work must have been acknowledged by emailing transcripts.</li>
                            <li>File must have been returned on time, following all instructions given when it was assigned.</li>
                            <li>Assigning email must be included when file is returned to transcripts with email subject matching name of your file.</li>
                            <li>Checklist must have been ticked off upon a proofread before saving/sending your file.</li>
                            <li>Notetakers must send in the required table for live notes when returning each of their live files.</li>
                           </ul>'
            ),
            'id = :id',
             array('id' => 1)
        );

        $this->update('report_criteria', array(
            'criteria' => '<ul><li>Spellings must be accurate. </li>
                            <li>File must have been spellchecked and all red lines omitted once you are sure spellings are correct.</li>
                           </ul>',
            ),
            'id = :id',
             array('id' => 2)
        );

        $this->update('report_criteria', array(
            'criteria' => '<ul>
                            <li>Full files must be typed word for word.  Notes files and summaries must make sense and be clear and concise.</li>
                            <li>For the odd missed/misheard word, 1 point will be deducted.</li>
                            <li>For obvious accuracy issues that have taken time to correct, both points will be lost.</li>
                            <li>For inaccuracy on a higher level, overall score will be much lower and file will be sent back or retyped.</li>
                            </ul>',
            ),
            'id = :id',
             array('id' => 3)
        );

        $this->update('report_criteria', array(
            'criteria' => '<ul>
                            <li>All names/brands, etc. must be thoroughly researched and spelt correctly to receive this point.</li>
                            <li>Spellings must be correct and consistent throughout.</li></ul>',
            ),
            'id = :id',
             array('id' => 4)
        );

        $this->update('report_criteria', array(
            'criteria' => '<ul>
                            <li>Formatting should be fully in line with the guidelines.</li>
                            <li>For the odd error, 1 point will be deducted.</li>
                            <li>For more than this, both points will be lost.</li>
                            <li>Pay attention to template used, name of file (using Title Case if applicable), Transcriber Comments, ID of participants, font formatting, starter/ten-min timecodes, quotation marks/inverted commas, talking over each other, adverts, numbers, monetary amounts, times of day, etc.</li>
                           </ul>',
            ),
            'id = :id',
             array('id' => 5)
        );

        $this->update('report_criteria', array(
            'criteria' => '<ul>
                            <li>Each sentence must be well-structured and adequately punctuated.</li>
                            <li>All sentences must end with the correct punctuation.</li>
                            <li>Commas and the -, format must be used correctly and unnecessary false starts omitted.</li>
                            <li>Maximum length of three lines for sentences and fifteen lines for paragraphs must have been adhered to where possible.</li>
                           </ul>',
            ),
            'id = :id',
             array('id' => 6)
        );

        $this->update('report_criteria', array(
            'criteria' => '<ul>
                            <li>Apostrophes used correctly throughout, to indicate possession and missing letters.</li>
                            <li>Ensure correct spellings are used with use of its/it’s, their/there/they’re, you’re/your, etc.</li>
                            <li>Grammatical errors made by the speaker should be corrected.</li>
                            <li>Avoid use of And/But at the start of sentences, where possible.</li>
                           </ul>',
            ),
            'id = :id',
             array('id' => 7)
        );

        $this->update('report_criteria', array(
            'criteria' => '<ul>
                            <li>Inaudible, missed word and phonetic guess formatting must be correct and noted at the correct times at which these occurred.</li>
                            <li>All of these must be valid and not easy to decipher when we listen to them.</li>
                           </ul>',
            ),
            'id = :id',
             array('id' => 8)
        );
    }
}