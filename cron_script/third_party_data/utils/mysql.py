#!/usr/bin/env python
# -*- coding:utf-8 -*-
import MySQLdb
OperationalError = MySQLdb.OperationalError

class MySQL:
	__pool=None
	def __init__(self,host,user,password,port=3306,charset="utf8",pool=False,db="test"):
		self.host=host
		self.port=port
		self.user=user
		self.password=password
		self.charset=charset
		self.connectionPool=pool
		self.db=db
		try:
			if self.connectionPool is True and MySQL.__pool is None:
#                creator: 可以生成 DB-API 2 连接的任何函数或 DB-API 2 兼容的数据库连接模块。
#                mincached : 启动时开启的空连接数量(缺省值 0 意味着开始时不创建连接)
#                maxcached: 连接池使用的最多连接数量(缺省值 0 代表不限制连接池大小)
#                maxshared: 最大允许的共享连接数量(缺省值 0 代表所有连接都是专用的)如果达到了最大数量，被请求为共享的连接将会被共享使用。
#                maxconnections: 最大允许连接数量(缺省值 0 代表不限制)
#                blocking: 设置在达到最大数量时的行为(缺省值 0 或 False 代表返回一个错误；其他代表阻塞直到连接数减少)
#                maxusage: 单个连接的最大允许复用次数(缺省值 0 或 False 代表不限制的复用)。当达到最大数值时，连接会自动重新连接(关闭和重新打开)
#                setsession: 一个可选的SQL命令列表用于准备每个会话，如 ["set datestyle to german", ...]
#                creator 函数或可以生成连接的函数可以接受这里传入的其他参数，例如主机名、数据库、用户名、密码等。你还可以选择传入creator函数的其他参数，允许失败重连和负载均衡。
				from DBUtils.PersistentDB import PersistentDB
				__pool=PersistentDB(MySQLdb,host=self.host,user=self.user,passwd=self.password,port=self.port,db=self.db)

				#from DBUtils.PooledDB import PooledDB
				#__pool = PooledDB(MySQLdb,host=self.host,user=self.user,passwd=self.password,port=self.port)
				self.conn=__pool.connection()
			else:
				self.conn=MySQLdb.connect(host=self.host,port=self.port,user=self.user,passwd=self.password,db=self.db)
			#self.conn.autocommit(False)
			#self.conn.set_character_set(self.charset)
			self.cur=self.conn.cursor()
		except MySQLdb.Error as e:
			print("Mysql Error %d: %s" % (e.args[0], e.args[1]))

	def __del__(self):
		self.close()
	def selectDb(self,db):
		try:
			self.conn.select_db(db)
		except MySQLdb.Error as e:
			print("Mysql Error %d: %s" % (e.args[0], e.args[1]))

	def queryNotCatch(self,sql):
		n=self.cur.execute(sql)
		return n

	def query(self,sql):
		try:
			n=self.cur.execute(sql)
			return n
		except MySQLdb.Error as e:
			print("Mysql Error:%s\nSQL:%s" %(e,sql))

	def fetchRow(self):
		result = self.cur.fetchone()
		return result

	def fetchAll(self):
		result=self.cur.fetchall()
		desc =self.cur.description
		d = []
		for inv in result:
			_d = {}
			for i in range(0,len(inv)):
				_d[desc[i][0]] = str(inv[i])
			d.append(_d)
		return d

	def insert(self,table_name,data):
		columns=data.keys()
		_prefix="".join(['INSERT INTO `',table_name,'`'])
		_fields=",".join(["".join(['`',column,'`']) for column in columns])
		_values=",".join(["%s" for i in range(len(columns))])
		_sql="".join([_prefix,"(",_fields,") VALUES (",_values,")"])
		_params=[data[key] for key in columns]
		return self.cur.execute(_sql,tuple(_params))

	def update(self,tbname,data,condition):
		_fields=[]
		_prefix="".join(['UPDATE `',tbname,'`','SET'])
		for key in data.keys():
			_fields.append("%s = %s" % (key,data[key]))
		_sql="".join([_prefix ,_fields, "WHERE", condition ])

		return self.cur.execute(_sql)

	def delete(self,tbname,condition):
		_prefix="".join(['DELETE FROM  `',tbname,'`','WHERE'])
		_sql="".join([_prefix,condition])
		return self.cur.execute(_sql)

	def getLastInsertId(self):
		return self.cur.lastrowid

	def rowcount(self):
		return self.cur.rowcount

	def commit(self):
		self.conn.commit()

	def rollback(self):
		self.conn.rollback()

	def close(self):
		self.cur.close()
		self.conn.close()
def conn(host="127.0.0.1",port=3306,user="root",pwd="12345678",charset="utf8",pool=True,db="test"):
	conn=None
	try:
		conn=MySQL(host,user,pwd,port,charset,pool,db)
	except Exception, e:
		import logging
		logging.error("mysql has gone away host:"+host+" port:"+str(port)+" error:"+str(e))
	return conn

