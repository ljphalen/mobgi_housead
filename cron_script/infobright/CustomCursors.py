#!/usr/bin/env python
#_*_ coding:utf-8 _*_

import sys 
reload(sys) 
sys.setdefaultencoding('utf-8') 
import os
import time
import re
import MySQLdb
restr = r"""
	\s
	values
	\s*
	(
		\(
			[^()']*
			(?:
				(?:
						(?:\(
							# ( - editor hightlighting helper
							[^)]*
						\))
					|
						'
							[^\\']*
							(?:\\.[^\\']*)*
						'
				)
				[^()']*
			)*
		\)
	)
	\s
	on duplicate key update
	\s*
	(
		\(
			[^()']*
			(?:
				(?:
						(?:\(
							# ( - editor hightlighting helper
							[^)]*
						\))
					|
						'
							[^\\']*
							(?:\\.[^\\']*)*
						'
				)
				[^()']*
			)*
		\)
	)
"""

insert_values = re.compile(restr, re.S | re.I | re.X)


class CustomCursors(MySQLdb.cursors.Cursor):
	def executemany(self, query, args):

		"""Execute a multi-row query.
		
		query -- string, query to execute on server

		args

			Sequence of sequences or mappings, parameters to use with
			query.
			
		Returns long integer rows affected, if any.
		
		This method improves performance on multiple-row INSERT and
		REPLACE. Otherwise it is equivalent to looping over args with
		execute().

		"""
		del self.messages[:]
		db = self._get_db()
		if not args: return
		charset = db.character_set_name()
		if isinstance(query, unicode): query = query.encode(charset)
		m = insert_values.search(query)
		if not m:
			r = 0
			for a in args:
				r = r + self.execute(query, a)
			return r
		p = m.start(1)
		e = m.end(1)
		qv = m.group(1)
		try:
			q = [ qv % db.literal(a) for a in args ]
		except TypeError, msg:
			if msg.args[0] in ("not enough arguments for format string",
							   "not all arguments converted"):
				self.errorhandler(self, ProgrammingError, msg.args[0])
			else:
				self.errorhandler(self, TypeError, msg)
		except (SystemExit, KeyboardInterrupt):
			raise
		except:
			exc, value, tb = sys.exc_info()
			del tb
			self.errorhandler(self, exc, value)
		r = self._query('\n'.join([query[:p], ',\n'.join(q), query[e:]]))
		if not self._defer_warnings: self._warning_check()
		return r