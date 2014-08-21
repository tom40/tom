<?php

class m130529_133842_report_text_update extends CDbMigration
{

	public function safeUp()
    {
        $criteria = '<ul>
                    <li>File must have been <span class="highlight">returned on time, following all instructions given</span> when it was assigned.</li>
                    <li><span class="highlight">Checklist must have been ticked off</span> upon a proofread before saving/sending your file.</li>
                    <li><span class="highlight">Notetakers must send in the required table for live notes</span> when returning each of their live files.</li>
                    </ul>';
        return $this->update('report_criteria', array('criteria' => $criteria), "id = '1'");
    }

    public function safeDown()
    {
        $criteria = '<ul><li>Work must have been <span class="highlight">acknowledged</span> by emailing transcripts.</li>
                    <li>File must have been <span class="highlight">returned on time, following all instructions given</span> when it was assigned.</li>
                    <li><span class="highlight">Assigning email must be included</span> when file is returned to <span class="highlight">transcripts</span> with email subject matching name of your file.</li>
                    <li><span class="highlight">Checklist must have been ticked off</span> upon a proofread before saving/sending your file.</li>
                    <li><span class="highlight">Notetakers must send in the required table for live notes</span> when returning each of their live files.</li>
                    </ul>';
        return $this->update('report_criteria', array('criteria' => $criteria), "id = '1'");
	}
}