/***************************************************************************
 *                            DonationGiftStone.cs
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
using Server.Items;
using Server.Mobiles;
using Server.Accounting;
using Server.Engines.Quests;
using Server.Engines.Quests.Necro;
using Server.Engines.PlayerDonation;
using Server.Gumps;

namespace Server.Items
{
	public class DonationGiftStone : Item
	{
		public override string DefaultName
		{
			get { return "Double click this stone to redeem your donation gift here"; }
		}

		[Constructable]
        public DonationGiftStone() : base(0xED4)
		{
			Movable = false;
			Hue = 0x489;
		}

        public override void OnDoubleClick(Mobile from)
        {
			//check database for this player's account
			Accounting.Account account = from.Account as Accounting.Account;
			string accountName = account.Username;
			
			from.SendGump( new DonationStoreGump( from ) );
        }
		
		public DonationGiftStone(Serial serial) : base(serial)   
		{
		}

		public override void Serialize( GenericWriter writer )
		{
			base.Serialize( writer );

			writer.Write( (int) 0 ); // version
		}

		public override void Deserialize( GenericReader reader )
		{
			base.Deserialize( reader );

			int version = reader.ReadInt();
		}
	}
}



namespace Server.Gumps
{
	public class DonationStoreGump : Gump
	{
		private Mobile m_From;
		private long[] m_GiftIDs = new long[5];
		
		public DonationStoreGump(Mobile from)
			: base( 0, 0 )
		{
			m_From = from;
			
			from.CloseGump( typeof( DonationStoreGump ) );
			
			this.Closable=true;
			this.Disposable=true;
			this.Dragable=true;
			this.Resizable=false;
			this.AddPage(0);
			AddBackground(26, 25, 397, 434, 9200);
			AddLabel(141, 34, 38, @"Donation Store");
			AddHtml( 62, 62, 325, 60, @"If you have donated to this shard, you can retrieve your item here. Thank you for keeping this shard running!", (bool)true, (bool)true);
			AddLabel(62, 130, 38, @"Select to retrieve your item:");
			
			generatGiftList(from);

		}
		
		
		private void generatGiftList(Mobile acct)
		{
			string username = acct.Account.Username;
			DonationGift giftInfo = null;
			int offset = 40;
			
			ArrayList giftList = DonationStore.GetDonationGiftList(username);
			if (giftList.Count == 0)
			{
				AddHtml( 62, 162, 325, 60, @"Thank you for playing! You have no donation gift to claim now. Consider donating to this shard to keep this shard running.", (bool)true, (bool)true);
				return;
			}
			
			for (int i=1; (i < 6 && i <= giftList.Count); i++)
			{
				giftInfo = (DonationGift)giftList[i-1];
				m_GiftIDs[i-1] = giftInfo.Id;
				AddGiftOption(giftInfo.Name, i, offset);
			}
		}
		
		private void AddGiftOption(string itemName, int index, int offset)
		{
			AddButton( 62, 166+(offset*(index-1)), 4005, 4007, index, GumpButtonType.Reply, index );	//check this to get the gift
			AddLabel(94, 166+(offset*(index-1)), 0, itemName);
		}
		
		public override void OnResponse( Server.Network.NetState sender, RelayInfo info )
		{
			if (info.ButtonID > 0 && info.ButtonID <= 5)
			{
				m_From.CloseGump( typeof( DonationStoreGump ) );
				
				long giftId = m_GiftIDs[info.ButtonID-1];
				IEntity gift = DonationStore.RedeemGift(giftId, m_From.Account.Username);
				if (gift != null)
				{
					m_From.AddToBackpack((Item)gift);
				}
			}
		}

	}
}