/***************************************************************************
 *                              DonationGift.cs
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
using System.Collections;
using Server;
using Server.Network;

namespace Server.Engines.PlayerDonation
{
	public class DonationGift
	{
		private int giftId;
		private int giftType;
		private string giftName;
		private string className;
		
		
		public DonationGift(int id, int type, string name)
		{
			this.giftId = id;
			this.giftType = type;
			this.giftName = name;
		}
		
		public int Id
		{
			get { return giftId; }
			set { giftId = value; }
		}
		
		public int Type
		{
			get { return giftType; }
			set { giftType = value; }
		}
		
		public string Name
		{
			get { return giftName; }
			set { giftName = value; }
		}
	}
	
	
}