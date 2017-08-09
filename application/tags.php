<?php
return [
    /* 初始化 */
	'app_init' => [
	    'app\\common\\behavior\\AppInit'
	],
    /* 登入行为 */
    'memberLogin'  => ['app\\common\\behavior\\Login'],
    'anchorLogin'  => ['app\\common\\behavior\\Login'],
    'companyLogin' => ['app\\common\\behavior\\Login'],
    /* 专案行为 */
    'createProject' => ['app\\common\\behavior\\Project'],
    'checkProject'  => ['app\\common\\behavior\\Project'],
    'applyProject'  => ['app\\common\\behavior\\Project'],



];