CREATE TABLE users(
  user_id INT unsigned not null auto_increment,
  PRIMARY KEY(user_id),
  username VARCHAR(20) not null,
  UNIQUE KEY(username),
  password tinytext not null,
  displayname tinytext not null,
  email VARCHAR(255) not null,
  UNIQUE KEY(email),
  activation_code CHAR(32) not null,
  new_email VARCHAR(255),
  confirmation_code CHAR(32),
  directory_list ENUM('yes','no') not null default 'yes',
  KEY(directory_list),
  display_email ENUM('yes','no') not null default 'no'
) ENGINE=InnoDB;

INSERT INTO users (`username`,`password`,`displayname`,`email`) VALUES
  ('root','0x37353035643634613534653036316237616364353463636435386234396463343335303062363335','displayname','');

CREATE TABLE roles(
  role_id VARCHAR(40) not null,
  PRIMARY KEY(role_id),
  role_description TINYTEXT not null
) ENGINE=InnoDB;

INSERT INTO roles (`role_id`,`role_description`) VALUES
  ('sysop','System Operator'),
  ('user:active','Active User');

CREATE TABLE user_roles(
  user_id INT unsigned not null,
  role_id VARCHAR(40) not null,
  PRIMARY KEY(user_id, role_id)
) ENGINE=InnoDB;

INSERT INTO user_roles (`user_id`,`role_id`) VALUES
  ('1','sysop'),
  ('1','user:active');

CREATE TABLE sessions(
  session_id char(40) not null,
  PRIMARY KEY(session_id),
  user_id int unsigned not null,
  started int unsigned not null,
  lastact int unsigned not null,
  KEY(lastact),
  useragent tinytext not null,
  ipaddr varchar(39) not null
) ENGINE=InnoDB;

CREATE TABLE pages(
  page_id VARCHAR(255) not null,
  PRIMARY KEY(page_id),
  page_revision INT unsigned not null,
  page_updated INT unsigned not null,
  page_indexed ENUM('yes', 'no') not null default 'yes',
  KEY(page_indexed),
  page_read_access TEXT not null
) ENGINE=InnoDB;

INSERT INTO pages (`page_id`,`page_revision`,`page_updated`) VALUES
  ('index','1',UNIX_TIMESTAMP()),
  ('credits','2',UNIX_TIMESTAMP()),
  ('wiki_markup','3',UNIX_TIMESTAMP());

CREATE TABLE page_revisions(
  rev_id BIGINT unsigned not null auto_increment,
  PRIMARY KEY(rev_id),
  page_id VARCHAR(255) not null,
  KEY(page_id),
  rev_page_name TINYTEXT not null,
  rev_page_tagline TINYTEXT not null,
  rev_page_content TEXT not null,
  rev_date INT unsigned not null,
  rev_user INT unsigned not null
) ENGINE=InnoDB;

INSERT INTO page_revisions (`page_id`,`rev_page_name`,`rev_page_tagline`,`rev_page_content`,`rev_date`,`rev_user`) VALUES
  ('index','Krai Framework Demo Application Index','The index page.','\r\n\r\nThis is primarily as a demo application for the Krai Framework.\r\n',UNIX_TIMESTAMP(),'1'),
  ('credits','Site Credits','Good stuff other people have done.','This site uses the following software.\n      \n* **Krai Framework edge** by Greg McWhirter (http://Krai.hallofkvasir.org, projects -at- hallofkvasir -dot- org) used under the terms of the MIT License.\n* **Text_Wiki 1.2.0** by Paul M. Jones (http://pear.php.net/package/Text_Wiki/, pmjones -at- php -dot- net) used under the GNU LGPL version 2.1.\n* **Nakor&#39;s Input Scrubber 1.0** by Nakor (nakor -at- clantemplates -dot- com), modified by Greg McWhirter.\n* **jQuery 1.2.6 w/ Plugins** (http://jquery.com) used under the terms of the MIT License.\n * DOM Element Creator 0.3.1.\n * TextAreaResizer\n',UNIX_TIMESTAMP(),'1'),
  ('wiki_markup','Wiki Markup Examples','All the markup that&#39;s fit to print','[[toc]]\n----\n+++ Inline Markup\n\n|| Italics || ``//This text is italic//`` || //This text is italic// ||\n|| Bold || ``**This text is bold**`` || **This text is bold** ||\n|| TeleType || ``{{This text is teletype}}`` || {{This text is teletype}} ||\n|| Delete + Insert || ``@@--- delete text +++ insert text @@`` || @@--- delete text +++ insert text @@ ||\n|| Delete Only || ``@@--- delete only @@`` || @@--- delete only @@ ||\n|| Insert Only || ``@@+++ insert only @@`` || @@+++ insert only @@ ||\n----\n+++ Literal Text\n\n|| ``This //text// gets **parsed**`` || This //text// gets **parsed** ||\n|| ` ` ``This //text// does not get **parsed**.`` ` ` || ``This //text// does not get **parsed**.`` ||\n----\n+++ Headings\n\n``+++ Level 3``\n+++ Level 3\n``++++ Level 4``\n++++ Level 4\n``+++++ Level 5``\n+++++ Level 5 \n``++++++ Level 6``\n++++++ Level 6\n----\n+++ Table of Contents\n\n``[[toc]]``\nSee the top of the page for example.\n----\n+++ Lists\n\n++++ Bullet Lists\n``* Item 1``\n``* Item 2``\n[[nbsp]]``* Sub-Item 2-1``\n``* Item 3``\n\n* Item 1\n* Item 2\n * Sub-Item 2-1\n* Item 3\n\n++++ Numbered Lists\n\n``# Item 1``\n``# Item 2``\n[[nbsp]]``# Item 3``\n``# Item 4``\n\n# Item 1\n# Item 2\n # Sub-Item 2-1\n# Item 3\n\n++++ Mixed Lists\n\nLists can successfully be mixed as well.\n----\n+++ Definitions\n\n``: Item 1 : Some definition``\n``: Item 2 : Some definition``\n\n: Item 1 : Some definition\n: Item 2 : Some definition\n----\n+++ URLs\n\n``http://hallofkvasir.org``\n``mailto:system@hallofkvasir.org``\n``[http://slashdot.org Slashdot]``\n``[mailto:system@hallofkvasir.org Site Admin]``\n``[http://slashdot.org]``\n\nhttp://hallofkvasir.org\nmailto:system@hallofkvasir.org\n[http://slashdot.org Slashdot]\n[mailto:system@hallofkvasir.org Site Admin]\n[http://slashdot.org]\n----\n+++ Images\n\n``http://www.hallofkvasir.org/h/images/hoklogo1.png``\n``[http://www.hallofkvasir.org/h/images/hoklogo1.png Hall of Kvasir]``\n\nhttp://www.hallofkvasir.org/h/images/hoklogo1.png\n[http://www.hallofkvasir.org/h/images/hoklogo1.png Hall of Kvasir]\n----\n+++ Tables\n\n``|| Cell One || Cell Two || Cell Three ||``\n``|||| Cell Four || Cell 5 ||``\n``|| Cell 6 |||| Cell 7 ||``\n``|||||| Cell 8 ||``\n\n|| Cell One || Cell Two || Cell Three ||\n|||| Cell Four || Cell 5 ||\n|| Cell 6 |||| Cell 7 ||\n|||||| Cell 8 ||\n\n\n----\n',UNIX_TIMESTAMP(),'1');


CREATE TABLE page_owners(
  page_id VARCHAR(255) not null,
  user_id INT unsigned not null,
  PRIMARY KEY(page_id, user_id)
) ENGINE=InnoDB;
