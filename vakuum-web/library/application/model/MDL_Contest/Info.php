<?php

class MDL_Contest_Info
{
	function def()
	{
		$config_oi = array
		(
			'time' => array
			(
				'sign_up_start' => 0,
				'sign_up_end' => time() + 1000,
				'contest_start' => time() + 500,
				'contest_end' => time() + 10000,
				'contest_time_limit' => 0,
			),
			'rules' => array
			(
				'case_point' => true,
				'penalty_time' => '0',
			),
			'permissions' => array
			(
				'during' => array
				(
					'record_display' => 31,
					'rank_display' => 0,
				),
				'end' => array
				(
					'record_display' => 31,
					'rank_display' => 0,
				),
			),
			'others' => array
			(
				'realtime_judge' => true,
			),
		);
	}

	public function __toString()
	{
		return serialize($this);
	}

	public function __sleep()
	{
		return array('config');
	}

	protected $config;
	protected $problems = NULL;

	public function __construct($config_string = '')
	{
		if ($config_string != '')
		{
			$this->config = $this->decodeConfig($config_string);
		}
	}

	protected function decodeConfig($config_string)
	{
		$rs = BFL_XML::XML2Array($config_string);
		if (!isset($rs['problems'][0]))
			$rs['problems'] = array($rs['problems']);
		return $rs;
	}

	public function getName()
	{
		return $this->config['name'];
	}

	public function getDesc()
	{
		return $this->config['desc'];
	}

	public function getSignUpTimeStart()
	{
		return $this->config['time']['sign_up_start'];
	}

	public function getSignUpTimeEnd()
	{
		return $this->config['time']['sign_up_end'];
	}

	public function getContestTimeStart()
	{
		return $this->config['time']['contest_start'];
	}

	public function getContestTimeEnd()
	{
		return $this->config['time']['contest_end'];
	}

	public function getContestTimeLimit()
	{
		return $this->config['time']['contest_time_limit'];
	}

	public function getPenaltyTime()
	{
		return $this->config['rules']['penalty_time'];
	}

	public function getProblems()
	{
		if ($this->problems == NULL)
		{
			foreach ($this->config['problems'] as $i => $problem)
			{
				$prob_id = $problem['prob_id'];
				$alias = $problem['alias'];
				$score = $problem['score'];

				$this->problems[$i] = new MDL_Problem($prob_id);
				$this->problems[$i]->alias = $alias;
				$this->problems[$i]->score = $score;
			}
		}

		return $this->problems;
	}

	public function getProbIDbyAlias($prob_alias)
	{
		foreach ($this->config['problems'] as $i => $problem)
		{
			if ($prob_alias == strtolower($problem['alias']))
				return $problem['prob_id'];
		}
		throw new MDL_Exception_Contest(MDL_Exception_Contest::INVALID_PROB_ALIAS);
	}

	public function countCaseScore()
	{
		return (bool) $this->config['rules']['case_point'];
	}

	public function isDuringSignUp($t_time)
	{
		return $t_time >= $this->config['time']['sign_up_start'] &&
				$t_time < $this->config['time']['sign_up_end'];
	}

	public function isDuringContest($t_time)
	{
		return $t_time >= $this->config['time']['contest_start'] &&
				$t_time < $this->config['time']['contest_end'];
	}

	public function getRankDisplay($period)
	{
		return new MDL_Contest_Rank_Display($this->config['permissions'][$period]['rank_display']);
	}

	public function getRecordDisplay($period)
	{
		return new MDL_Contest_Rank_Display($this->config['permissions'][$period]['record_display']);
	}
}