/***************************************************************************
 *                             DonationStore.cs
 *                            -------------------
 *   begin                : Oct 24, 2009
 *   copyright            : (C) Antony Ho
 *   email                : ntonyworkshop@gmail.com
 *   website              : http://antonyho.net/
 *
 ***************************************************************************/
 
/***************************************************************************
 *
 *   Copyright (C) Ho Man Chung
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *   
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *   GNU General Public License for more details.
 *   
 *   You should have received a copy of the GNU General Public License
 *   along with this program. If not, see <http://www.gnu.org/licenses/>.
 ***************************************************************************/

using System;
using System.Data;
using System.Data.Odbc;
using System.Net;
using System.Collections;
using System.Xml;
using System.Text;
using System.Reflection;
using Server;
using Server.Misc;
using Server.Network;
using Server.Commands;

namespace Server.Engines.PlayerDonation
{
	//public delegate Item ConstructCallback();
	
	public class DonationStore
	{
		
		private static string
			DatabaseDriver = "{MySQL ODBC 3.51 Driver}",
			DatabaseServer = "dbexample.hkuoshard.com",	// your MySQL database hostname
			DatabaseName = "hkuoshard_example_db",			// the database name of your donation store db
			DatabaseUserID = "example",				// username for your MySQL database access
			DatabasePassword = "randomPassword";	// password

		static string ConnectionString = String.Format( "driver={0};server={1};database={2};uid={3};pwd={4}",
			DatabaseDriver, DatabaseServer, DatabaseName, DatabaseUserID, DatabasePassword );
		
		public static ArrayList GetDonationGiftList(string username)
		{
			//get a list of item from redeemable_gift table
			ArrayList redeemableGifts = new ArrayList();
			
			IDbConnection connection = null;
			IDbCommand command = null;
			IDataReader reader = null;
			try
			{
				connection = new OdbcConnection( ConnectionString );

				connection.Open( );
				
				
				command = connection.CreateCommand( );
				
				command.CommandText = String.Format("SELECT redeemable_gift.id AS id, redeemable_gift.type_id AS type, gift_type.type_name AS name FROM redeemable_gift INNER JOIN gift_type ON redeemable_gift.type_id=gift_type.type_id WHERE redeemable_gift.account_name='{0}' ORDER BY redeemable_gift.id ASC", username);
				reader = command.ExecuteReader();
				
				while (reader.Read())
				{
					int giftId = System.Convert.ToInt32(reader["id"]);
					int giftTypeId = System.Convert.ToInt32(reader["type"]);
					string giftName = (string)reader["name"];
					DonationGift gift = new DonationGift(giftId, giftTypeId, giftName);
					redeemableGifts.Add(gift);
				}
				reader.Close();
			}
			catch( Exception e )
			{
				Console.WriteLine( "[Retrieve Donation Gift List] Error..." );
				Console.WriteLine( e );
			}
			finally
			{
				if (reader != null && !reader.IsClosed)
					reader.Close();
				if (command != null && connection != null)
				{
					command.Dispose();
					connection.Close();
				}
			}
			
			return redeemableGifts;
		}
		
		public static IEntity RedeemGift(long giftId, string username)
		{
			// move the record from redeenable_gift table to redeemed_gift table
			IDbConnection connection = null;
			IDbCommand command = null;
			IDataReader reader = null;
			
			IEntity gift = null;
			
			try
			{
				connection = new OdbcConnection( ConnectionString );

				connection.Open( );
				command = connection.CreateCommand( );
				
				//get the gift type by selecting redeemable_gift table using id
				command.CommandText = String.Format("SELECT type_id,donate_time,paypal_txn_id FROM redeemable_gift WHERE id='{0}' AND account_name='{1}'", giftId, username);
				reader = command.ExecuteReader();
				
				int typeId;
				int donateTime;
				string paypalTxnId = string.Empty;
				
				if (reader.Read())
				{
					typeId = System.Convert.ToInt32(reader["type_id"]);
					donateTime = System.Convert.ToInt32(reader["donate_time"]);
					paypalTxnId = (string)reader["paypal_txn_id"];
				}
				else
				{
					Console.WriteLine(String.Format("[Redeem Donation Gift] No such Gift(ID:{0}) for Account Name: {1}", giftId, username));
					return null;
				}
				reader.Close();
				command.Dispose();
				
				// insert record to redeemed_gift first
				command = connection.CreateCommand( );
				IDbTransaction transaction = connection.BeginTransaction();
				command.Connection = connection;
				command.Transaction = transaction;
				DateTime currTime = DateTime.Now;
				
				string classConstructString = GetClassNameByType(typeId);
				gift = getGiftInstance(classConstructString);
				if ( gift == null)
				{
					Console.WriteLine(String.Format("[Redeem Donation Gift] Unable to finished the process. Gift(ID:{0}) for Account Name: {1}", giftId, username));
				}
				
				//get the Serial from its instance
				Serial serial = gift.Serial.Value;
				
				//update the serial to database for your later tracking
				command.CommandText = String.Format("INSERT INTO redeemed_gift (id,type_id,account_name,donate_time,redeem_time,serial,paypal_txn_id) VALUES ('{0}','{1}','{2}','{3}','{4}','{5}','{6}')", giftId, typeId, username, donateTime, Convert.ToInt32(ToUnixTimestamp(currTime)), serial.ToString(), paypalTxnId);
				if (command.ExecuteNonQuery() != 1)
				{
					Console.WriteLine(String.Format("[Redeem Donation Gift] (insert record to redeemed_gift) SQL Error. Unable to finished the process. Gift(ID:{0}) for Account Name: {1}", giftId, username));
					transaction.Rollback();
					return null;
				}
				
				//remove record from redeemable_gift
				command.CommandText = String.Format("DELETE FROM redeemable_gift WHERE id='{0}' AND account_name='{1}'", giftId, username);
				
				if (command.ExecuteNonQuery() != 1)
				{
					Console.WriteLine(String.Format("[Redeem Donation Gift] (remove record from redeemable_gift) SQL Error. Unable to finished the process. Gift(ID:{0}) for Account Name: {1}", giftId, username));
					transaction.Rollback();
					return null;
				}
				transaction.Commit();
			}
			catch( Exception e )
			{
				Console.WriteLine( "[Redeem Donation Gift] Error..." );
				Console.WriteLine( e );
			}
			finally
			{
				if (reader != null && !reader.IsClosed)
					reader.Close();
				if (command != null && connection != null)
				{
					command.Dispose();
					connection.Close();
				}
			}
			
			return gift;
		}
		
		public static string GetClassNameByType(int typeId)
		{
			IDbConnection connection = null;
			IDbCommand command = null;
			IDataReader reader = null;
			
			string className = string.Empty;
			
			try
			{
				connection = new OdbcConnection( ConnectionString );

				connection.Open( );
				command = connection.CreateCommand( );
				
				command.CommandText = String.Format("SELECT class_name FROM gift_type WHERE type_id='{0}'", typeId);
				reader = command.ExecuteReader();
				
				
				if (reader.Read())
				{
					className = (string)reader["class_name"];
				}
				else
				{
					Console.WriteLine(String.Format("[Retrieve Donation Gift Class Name] No such gift type: {0}", typeId));
					return null;
				}
				
				
				reader.Close();
				command.Dispose();
				connection.Close();
			}
			catch( Exception e )
			{
				Console.WriteLine( "[Retrieve Donation Gift Class Name] Error..." );
				Console.WriteLine( e );
			}
			finally
			{
				if (reader != null && !reader.IsClosed)
					reader.Close();
				if (command != null && connection != null)
				{
					command.Dispose();
					connection.Close();
				}
			}
			
			return className.Trim();
		}
		
		public static IEntity getGiftInstance(string classConstructString)
		{
			IEntity gift = null;
			//create the object of the gift by its name
			string[] classContructParams = classConstructString.Split(' ');	// use space as sperator
			string className = classContructParams[0];
			Type giftType = ScriptCompiler.FindTypeByName( className );
			ConstructorInfo[] ctors = giftType.GetConstructors();
			
			for ( int i = 0; i < ctors.Length; ++i )
			{
				ConstructorInfo ctor = ctors[i];

				if ( !Add.IsConstructable( ctor, AccessLevel.GameMaster ) )
					continue;

				ParameterInfo[] paramList = ctor.GetParameters();
				if ( paramList.Length == (classContructParams.Length - 1) )	// we don't use complex constructors to create the item
				{
					string[] args = new string[classContructParams.Length - 1];
					Array.Copy(classContructParams, 1, args, 0, args.Length);
					object[] param = Add.ParseValues(paramList, args);
					if (param == null)
						continue;
					object giftInstance = ctor.Invoke(param);
					if (giftInstance != null)
					{
						gift = (IEntity)giftInstance;
						break;
					}
					else
						return null;
				}
			}
			
			// get the accessor of this item and check whether it has IsDonation attribute
			PropertyInfo propInfo = giftType.GetProperty("IsDonationItem");
			if ( propInfo != null )
			{
				MethodInfo setterMethod = propInfo.GetSetMethod();
				bool isDonationItem = true;
				object[] parameters = new object[] { isDonationItem };
				setterMethod.Invoke(gift, parameters);
			}
			
			/*
			ConstructCallback cstr = new ConstructCallback( className );
			gift = cstr();
			*/
			
			return gift;
		}
		
		static double ToUnixTimestamp( DateTime date )
		{
			DateTime origin = new DateTime( 1970, 1, 1, 0, 0, 0, 0 );
			TimeSpan diff = date - origin;
			
			return Math.Floor( diff.TotalSeconds );
		}


	}
}
