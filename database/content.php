<?php

declare(strict_types=1);

/**
 * Seed editorial content. Long-form HTML is stored exactly as the CMS would
 * save it (allow-listed tags only).
 */

$article = static function (string $title, string $lede, array $sections, string $closing): string {
    $html = '<p class="lead">' . $lede . '</p>';
    foreach ($sections as $section) {
        $html .= '<h2>' . $section[0] . '</h2>';
        foreach ($section[1] as $paragraph) {
            $html .= '<p>' . $paragraph . '</p>';
        }
        if (!empty($section[2])) {
            $html .= $section[2];
        }
    }
    $html .= '<h2>Where this leaves us</h2><p>' . $closing . '</p>';
    return $html;
};

return [
    [
        'title' => 'The Psychology of Ritual: Why Symbolic Practice Still Shapes the Mind',
        'slug' => 'psychology-of-ritual',
        'category' => 'black-magic-occult',
        'author' => 'hamza',
        'date' => date('Y-m-d', strtotime('-8 days')),
        'image' => 'assets/img/blogs/occult-1.jpg',
        'featured' => true,
        'tags' => ['ritual', 'psychology', 'belief'],
        'excerpt' => 'Strip away the candles and the chanting and something remains: a technology of attention. A look at what ritual actually does to a human mind.',
        'content' => $article(
            'The Psychology of Ritual',
            'Every culture we know of has built rituals. That consistency is the interesting part — not whether a ritual "works" in the supernatural sense, but why the human mind keeps inventing them.',
            [
                ['Ritual is a container', [
                    'Anthropologists have long noticed that rituals share a structure: a threshold, a sequence of prescribed actions, and a return. The structure is what makes the practice portable across belief systems. Whether the frame is a séance, a Catholic mass, or a pre-flight checklist, the shape is recognisable.',
                    'Psychologists describe this as a <strong>container for uncertainty</strong>. When outcomes are uncontrollable — illness, conflict, love, death — ritual offers sequence. Sequence reduces cognitive load. Reduced load feels like control.',
                ]],
                ['The attention economy of the altar', [
                    'Lighting a candle at a specific moment forces a shift in attention. The act is deliberate, bounded and repeatable. Modern attention research keeps arriving at the same conclusion: deliberate, bounded attention is scarce and valuable.',
                    'That is why ritual can be genuinely useful even to a convinced sceptic. It is not that the candle does anything. It is that the person doing the lighting changes state — slower breathing, narrower focus, a moment that is not the ordinary present.',
                ], '<blockquote>Ritual does not require belief to be effective. It only requires attention.</blockquote>'],
                ['Placebo, expectation and the honest version', [
                    'The placebo effect is the most documented example of expectation changing experience. Ritual is a delivery system for expectation. When someone performs a protection ritual before a difficult week, they are rehearsing a story in which they are defended. Rehearsal changes behaviour: posture, boundaries, the decision to leave a room.',
                    'This is where occult writing usually splits into two bad options — debunking with contempt, or pretending certainty about forces nobody can measure. There is a third option: describe the mechanism as honestly as we can, and let readers decide what the symbol means to them.',
                ]],
                ['What research can and cannot tell us', [
                    'Controlled studies can measure anxiety, heart rate and reported sense of control around ritual behaviour. They cannot measure meaning. Meaning is not a variable you can average.',
                    'So the honest summary is narrow and useful: ritual reliably influences the person performing it. Claims about external effects remain claims, and should be labelled as such — by practitioners and by journalists alike.',
                ]],
                ['A field guide for the curious', [
                    'If you want to study ritual rather than mock or evangelise it, start here:',
                ], '<ul><li><strong>Ask what problem it solves.</strong> Is the ritual reducing fear, marking a transition, or coordinating a group?</li><li><strong>Separate the symbol from the claim.</strong> A pentagram is a shape with a history; whether it does anything beyond the mind is a separate question.</li><li><strong>Look at the edge cases.</strong> What happens when a ritual is interrupted or performed badly? Anxiety spikes — evidence that the structure, not the supernatural, is doing the work.</li><li><strong>Keep a journal.</strong> Note the state before and after. Your own data is more interesting than a viral thread about "ancient secrets".</li></ul>'],
            ],
            'Ritual is one of the oldest technologies we have for managing fear and marking change. You do not have to believe in spirits to respect the machinery. You only have to notice how carefully humans build rooms for their own attention.'
        ),
    ],
    [
        'title' => 'Grimoire to Archive: How Occult Knowledge Survived the Printing Press',
        'slug' => 'grimoire-to-archive',
        'category' => 'black-magic-occult',
        'author' => 'hamza',
        'date' => date('Y-m-d', strtotime('-21 days')),
        'image' => 'assets/img/blogs/occult-2.jpg',
        'featured' => false,
        'tags' => ['history', 'grimoires', 'books'],
        'excerpt' => 'Censorship rarely destroys a text. More often it turns the text into contraband, and contraband travels. A short history of occult publishing.',
        'content' => $article(
            'Grimoire to Archive',
            'The history of occult books is a history of copying. Long before the printing press, grimoires moved between monasteries, courts and kitchens as hand-copied bundles — often with errors, additions and local folklore stitched into the margins.',
            [
                ['The accidental resilience of manuscript culture', [
                    'Manuscripts are fragile but distributed. A single fire can destroy a library, yet the same text survives in a dozen private copies in a dozen towns. This redundancy is why fragments of supposedly destroyed works keep surfacing in archives.',
                    'Scribes also editorialised. A ritual copied in one region might acquire a local saint, a different herb, a new warning. Historians read these differences as evidence of how ideas travel and mutate — the same process we now study in memes.',
                ]],
                ['Printing as amplification and risk', [
                    'Moveable type made occult texts cheaper and more dangerous to their printers. A book that could be copied fifty times by hand was, by definition, semi-private. Printed in runs of hundreds, it became public in a new way.',
                    'Authorities responded with licensing, index lists and seizures. The unintended effect was marketing: a prohibited book signalled forbidden knowledge, and forbidden knowledge sold. Smuggled editions crossed borders in furniture, in wine crates, inside devotional volumes with false title pages.',
                ], '<blockquote>Censorship changed the distribution channel, not the demand.</blockquote>'],
                ['From grimoire to dataset', [
                    'Today the same texts sit in digitised archives, searchable and contextualised. That is a genuine improvement in access, and a genuine risk to interpretation: a passage stripped of its material context — the paper, the hand, the owner\'s notes — is easier to misread.',
                    'Digital humanities projects have started cataloguing annotations and provenance alongside the text. When you can see that a "spell" was added by a seventeenth-century owner with a different agenda, the text stops looking like a monolith and starts looking like a conversation.',
                ]],
                ['How to read an occult source responsibly', [
                    'Three habits separate research from roleplay:',
                ], '<ol><li><strong>Date the edition.</strong> A 1920s reprint is not a medieval manuscript. Print dates reveal what the editor wanted to add.</li><li><strong>Follow the footnotes.</strong> Reliable editors cite archives. Sloppy ones cite each other in a circle.</li><li><strong>Expect mundane explanations.</strong> Ink, patronage, copyright disputes and family feuds explain more of the occult canon than secret societies do.</li></ol>'],
            ],
            'Occult texts survived because they were copied, sold, banned, smuggled and reprinted. Understanding that economy is more revealing than any single ritual inside them — it shows how knowledge becomes culture, and how culture becomes myth.'
        ),
    ],
    [
        'title' => 'Salt, Iron and Thresholds: The Folklore of Protection',
        'slug' => 'salt-iron-thresholds',
        'category' => 'black-magic-occult',
        'author' => 'hamza',
        'date' => date('Y-m-d', strtotime('-47 days')),
        'image' => 'assets/img/blogs/occult-3.jpg',
        'featured' => false,
        'tags' => ['folklore', 'protection', 'home'],
        'excerpt' => 'Why do so many unrelated cultures agree that salt belongs at doors, iron repels danger, and thresholds are dangerous places?',
        'content' => $article(
            'Salt, Iron and Thresholds',
            'Three materials show up in protection folklore with suspicious regularity: salt, iron and the threshold itself. The scholarship around them is a useful case study in how practical life becomes symbolic.',
            [
                ['Salt was expensive before it was sacred', [
                    'Preservation, seasoning and survival made salt valuable. Anything valuable acquires ritual weight. Spilled salt became a small disaster, and the remedies invented for it — throwing a pinch over the shoulder — survive long after the economics changed.',
                    'In several traditions salt is placed at windows and doors. The practical reading: salt absorbs moisture and marks a boundary. The symbolic reading came later, as it usually does.',
                ]],
                ['Iron is a technology that scared people', [
                    'Iron tools changed agriculture and warfare. They were also the most advanced technology many communities possessed — expensive, loud, and associated with the people who worked them. Folklore about iron repelling spirits tracks closely with iron becoming commonplace.',
                    'Horseshoes over doors sit at the intersection of craft, luck and scrap economics. The shape is not magical. The story attached to the shape is.',
                ]],
                ['The threshold is a real psychological boundary', [
                    'Doorways are where categories blur: inside and outside, mine and not-mine, guest and intruder. Blurred categories are exactly where cultures place rules and rituals.',
                    'That is why so much protection folklore clusters at the door — and why the modern equivalent is a ring camera, a chain and a habit of checking who is at the gate before opening it.',
                ], '<blockquote>The threshold is the oldest security perimeter. Folklore simply gave it a story before we gave it a lock.</blockquote>'],
                ['What to take from it', [
                    'The useful insight is not that salt stops spirits. It is that communities encode practical safety advice in memorable form. A neighbour who tells you never to leave the door unlatched at dusk is transmitting a habit that has statistically mattered for centuries.',
                    'If you practice these customs, you are participating in a long conversation about safety and belonging. If you study them, you are watching that conversation become evidence.',
                ]],
                ['A note on respect', [
                    'Folklore belongs to communities, not to content farms. When writing about a tradition you do not belong to, name the source, avoid inventing a sinister origin, and do not turn living practice into a Halloween prop.',
                ]],
            ],
            'Salt, iron and thresholds persist because they were once practical, then became memorable, then became meaningful. That sequence explains most of what we call magic — and makes the folklore more interesting, not less.'
        ),
    ],
    [
        'title' => 'Reading the Cards: A Sceptic\'s Field Guide to Tarot Symbolism',
        'slug' => 'tarot-symbolism-field-guide',
        'category' => 'black-magic-occult',
        'author' => 'hamza',
        'date' => date('Y-m-d', strtotime('-95 days')),
        'image' => 'assets/img/blogs/occult-4.jpg',
        'featured' => false,
        'tags' => ['tarot', 'symbolism', 'scepticism'],
        'excerpt' => 'Tarot is a visual language with a history. You can study it seriously without deciding that the cards predict the future.',
        'content' => $article(
            'Reading the Cards',
            'Tarot decks are image systems, and image systems are unusually good at producing meaning. That is a fact about interpretation, not a claim about fortune.',
            [
                ['Where the images come from', [
                    'The standard 78-card structure emerged in fifteenth-century Europe as a card game. Its esoteric readings were layered on centuries later by authors with strong opinions and commercial instincts.',
                    'The famous Rider–Waite–Smith deck, published in 1909, is where most modern symbolism comes from. Pamela Colman Smith\'s illustrations did more to shape how people read tarot than any occult treatise.',
                ]],
                ['Why readings feel accurate', [
                    'Three mechanisms do most of the work:',
                ], '<ul><li><strong>Ambiguity.</strong> A card meaning "a difficult transition" can fit almost any life at almost any time.</li><li><strong>Participation.</strong> The reader watches your face, and you unconsciously steer the story toward what is already on your mind.</li><li><strong>Memory selection.</strong> You remember the hits and forget the misses — the same bias that makes horoscopes feel personal.</li></ul>'],
                ['Reading it as a mirror', [
                    'Strip away prediction and tarot remains a structured prompt for reflection. A card gives you a category — loss, ambition, patience — and you fill it with your situation. Therapists call the same process "externalising" and use it deliberately.',
                    'That is why some counsellors keep a deck on the table. Not because the deck knows anything, but because a card can ask a question that a person will not ask themselves.',
                ], '<blockquote>A card is not a message from elsewhere. It is a question from somewhere in you.</blockquote>'],
                ['How to study it', [
                    'Start with the major arcana and one theme: power, endings, choice. Learn the iconography the way you would learn any visual vocabulary — slowly, with sources.',
                    'Note what each card triggers in you before reading the book. Those notes are data about you, and they are more interesting than the official meaning.',
                ]],
                ['The ethical line', [
                    'Reflection is not the same as advice. If a reading starts making decisions about another person\'s health, money or safety, it has crossed from symbolism into risk. A good reader says so.',
                ]],
            ],
            'Tarot survives because humans need containers for difficult questions. You can respect that need, enjoy the imagery, and still keep both feet on the floor.'
        ),
    ],
    [
        'title' => 'Consent as a Skill: Practising Clear Communication Before Intimacy',
        'slug' => 'consent-as-a-skill',
        'category' => 'sexual-health-education',
        'author' => 'mymn',
        'date' => date('Y-m-d', strtotime('-4 days')),
        'image' => 'assets/img/blogs/health-1.jpg',
        'featured' => true,
        'tags' => ['consent', 'communication', 'relationships'],
        'excerpt' => 'Consent is not a checkbox at the start of an encounter. It is a conversational skill that can be learned, practised and improved.',
        'content' => $article(
            'Consent as a Skill',
            'Most people agree consent matters. Far fewer have rehearsed the sentences that make it work. Like any skill, it improves with practice — and it becomes easier when you stop treating it as an interruption.',
            [
                ['The myth of the single question', [
                    'A common image of consent is a single question asked once, usually awkwardly. In practice, consent is continuous: interests change during an encounter, and a yes to one thing is not a yes to everything.',
                    'Reframing consent as a running conversation removes pressure. You are not seeking a signature. You are checking alignment, the way you would when planning any shared activity.',
                ]],
                ['Language that actually works', [
                    'Short, specific and open-ended beats vague and grand:',
                ], '<ul><li>"I would like to ___ — are you into that?"</li><li>"How are you feeling about this so far?"</li><li>"Can we slow down for a moment?"</li><li>"That is a no for me, and I am glad I said it."</li></ul>'],
                ['Reading bodies without mind-reading', [
                    'Non-verbal cues matter, but they are easy to misread. Freezing is not enthusiasm. Silence is not agreement. A body that stops moving is a signal to stop and ask, not a signal to continue.',
                    'The rule of thumb is simple: when in doubt, pause and use words. The pause is not a mood-killer. It is often the moment people describe as the most respectful thing a partner has done.',
                ], '<blockquote>Consent is not the absence of a no. It is the active presence of a real yes.</blockquote>'],
                ['When consent is complicated', [
                    'Power differences, intoxication and past patterns can all distort a yes. A useful test: could this person say no to me comfortably, and would they expect that no to be respected? If the answer is uncertain, slow down and address it.',
                    'None of this is about assigning blame for normal awkwardness. It is about noticing when a situation is drifting away from mutuality and choosing to correct course.',
                ]],
                ['Practising outside the bedroom', [
                    'Consent skills transfer: asking for what you want in a restaurant, declining a plan without over-explaining, checking in with a friend before giving advice. The more you rehearse small boundaries, the easier the larger conversations become.',
                ]],
            ],
            'Consent is a practice, not a performance. The goal is not to pass a test but to build relationships where both people can be honest, change their minds, and still be safe. If you take one thing from this article, take the pause.'
        ),
    ],
    [
        'title' => 'The Science of Sexual Health Check-ups: What to Expect and When',
        'slug' => 'sexual-health-checkups',
        'category' => 'sexual-health-education',
        'author' => 'mymn',
        'date' => date('Y-m-d', strtotime('-33 days')),
        'image' => 'assets/img/blogs/health-2.jpg',
        'featured' => false,
        'tags' => ['testing', 'sexual-health', 'healthcare'],
        'excerpt' => 'A practical guide to routine sexual health screening: window periods, common tests, and how to make the appointment less awkward.',
        'content' => $article(
            'The Science of Sexual Health Check-ups',
            'Sexual health screening is one of the simplest forms of preventive care, and one of the most avoided. Most of the avoidance is about uncertainty — not knowing what happens, what is tested, or when to go.',
            [
                ['What a screening actually involves', [
                    'A routine check-up usually begins with a short conversation: symptoms, history, and what you want tested. Depending on the clinic, tests may include a urine sample, blood draw, or a swab. Many tests are painless and quick.',
                    'Clinicians are used to these conversations. You are not the first nervous person they have seen today, and you will not be the most awkward.',
                ]],
                ['Window periods explain the timing', [
                    'A window period is the time between exposure and when a test can reliably detect an infection. Testing too early can produce a false negative.',
                    'Commonly discussed windows (guidance varies by country and test type): HIV antigen tests around 4–6 weeks, syphilis around 4–6 weeks, chlamydia and gonorrhoea typically 1–2 weeks. Your clinic will confirm the right timing for the specific test used.',
                ], '<blockquote>Testing too early is the most common reason a result misses an infection.</blockquote>'],
                ['How often should you go?', [
                    'There is no single answer. A useful starting point is once a year for sexually active adults, more often with new or multiple partners, or after any exposure risk. If you have symptoms — pain, discharge, sores, unusual bleeding — do not wait for a routine appointment.',
                    'Many clinics offer free or low-cost screening. Cost is a legitimate barrier, and it is worth asking about sliding scales, community clinics and at-home test kits.',
                ]],
                ['Making it less uncomfortable', [
                    'Book the first appointment of the day if waiting rooms bother you. Write your questions down. Ask what is being tested and why. You can decline any test and ask what the trade-offs are.',
                    'Bringing a friend for the journey home, or planning something pleasant afterwards, is not childish. It is practical care.',
                ]],
                ['After the result', [
                    'Most results are negative, and a negative test is information, not a verdict. If something is positive, many common infections are curable with a short course of treatment, and the rest are manageable with modern care.',
                    'Notifying partners can feel daunting. Clinics often provide anonymous notification tools — and remember that telling someone is an act of respect, not a confession of wrongdoing.',
                ]],
            ],
            'Screening is not a judgement about your life. It is maintenance. The most useful thing you can do is learn your local clinic, know the window periods, and book the appointment you have been postponing.'
        ),
    ],
    [
        'title' => 'Desire Is Not a Scoreboard: Rethinking Performance in Relationships',
        'slug' => 'desire-not-a-scoreboard',
        'category' => 'sexual-health-education',
        'author' => 'mymn',
        'date' => date('Y-m-d', strtotime('-76 days')),
        'image' => 'assets/img/blogs/health-3.jpg',
        'featured' => false,
        'tags' => ['desire', 'intimacy', 'relationships'],
        'excerpt' => 'Comparison is the fastest way to ruin intimacy. What research actually says about desire, frequency and satisfaction.',
        'content' => $article(
            'Desire Is Not a Scoreboard',
            'A surprising amount of sexual anxiety comes from an imaginary average. How often should we? How long should it last? Is my desire normal? The questions sound factual, but they are usually about comparison.',
            [
                ['There is no healthy number', [
                    'Surveys of sexual frequency produce wide ranges and depend heavily on age, health, relationship length and culture. More importantly, frequency correlates weakly with satisfaction once basic needs are met.',
                    'What predicts satisfaction better is <strong>agreement</strong>: partners who feel their preferences are understood, and who can talk about mismatch without turning it into a verdict about the relationship.',
                ]],
                ['Spontaneous and responsive desire', [
                    'One of the most useful distinctions in sex therapy is between spontaneous desire (arriving before the encounter) and responsive desire (emerging during it, given the right conditions).',
                    'People with responsive desire often assume something is wrong with them because they rarely initiate. It is a different pattern, not a disorder. Knowing which pattern fits you can remove years of unnecessary self-criticism.',
                ], '<blockquote>Desire is a context, not a constant.</blockquote>'],
                ['Performance pressure is a shared problem', [
                    'Pressure to perform affects all genders, and it tends to narrow attention: monitoring yourself instead of being present. That monitoring is the opposite of arousal.',
                    'Breaking the loop usually involves lowering the stakes deliberately — time without expectation, affection that is not a prelude, and explicit permission to stop.',
                ]],
                ['Talking about mismatch', [
                    'Differences in desire are normal. The conversation that helps sounds like this: "I miss you, and I do not want this to become a fight. Can we talk about what makes intimacy easier for each of us?"',
                    'Avoid keeping score, avoid diagnosing your partner, and avoid using sex as evidence in unrelated arguments. Couples counselling is not a last resort; it is maintenance for a complex system.',
                ]],
                ['Health matters more than metrics', [
                    'Medication, sleep, stress, hormonal changes and pain all affect desire and function. If something changed suddenly, that is a clinical conversation, not a character flaw.',
                ]],
            ],
            'The goal is not a normal number. It is a relationship where desire can be discussed honestly, where mismatch is a logistics problem rather than an insult, and where both people feel safe enough to say what they actually want.'
        ),
    ],
    [
        'title' => 'How Ethical Hackers Scope a Target Before Touching a Keyboard',
        'slug' => 'scoping-ethical-hacking',
        'category' => 'hacking-cybersecurity',
        'author' => 'umar',
        'date' => date('Y-m-d', strtotime('-12 days')),
        'image' => 'assets/img/blogs/cyber-1.jpg',
        'featured' => true,
        'tags' => ['ethical-hacking', 'pentesting', 'scope'],
        'excerpt' => 'The most important document in a penetration test is not the report. It is the scope agreement that makes the work legal.',
        'content' => $article(
            'How Ethical Hackers Scope a Target',
            'Professional testing begins weeks before any tool runs. Scoping is where the legal, technical and human boundaries are defined — and where most engagements succeed or fail.',
            [
                ['Everything starts with authorisation', [
                    'Without written authorisation from someone who can legally grant it, testing is not testing. It is unauthorised access, regardless of intent.',
                    'A scope document should name the legal entity, the systems in scope, the time window, the contacts, the testing limits, and the handling rules for any data encountered. "The website" is not a scope. A list of hostnames and paths, with exclusions, is.',
                ]],
                ['Define the rules of engagement', [
                    'Important clauses include:',
                ], '<ul><li><strong>Time window.</strong> When testing may occur, including blackout periods around business-critical events.</li><li><strong>Techniques.</strong> Whether denial-of-service, social engineering or physical testing are permitted (usually not).</li><li><strong>Data handling.</strong> How to store, encrypt and destroy any personal data encountered.</li><li><strong>Escalation.</strong> Who to call, and how quickly, if a critical issue or real breach is discovered.</li><li><strong>Stop conditions.</strong> What causes testing to pause immediately.</li></ul>'],
                ['Reconnaissance is a legal minefield of its own', [
                    'Passive reconnaissance — public DNS, certificate transparency logs, job listings — is low risk. Active scanning against infrastructure you do not own can cross borders and laws quickly, especially with cloud hosting and third-party services.',
                    'Good testers confirm ownership of every IP and domain. Shared hosting, CDNs and SaaS dependencies are the usual places where scope leaks.',
                ], '<blockquote>The best testers spend more time confirming what they may touch than touching it.</blockquote>'],
                ['Build a threat model, not just a checklist', [
                    'A checklist finds known categories. A threat model asks who would attack this specific system, what they want, and what would make the attack easy. That guides where to spend limited hours.',
                    'Start from the business asset: customer data, payment flow, admin access, uptime. Then map the paths an attacker would take toward it.',
                ]],
                ['Reporting that gets fixes shipped', [
                    'A finding needs reproduction steps, impact in business language, evidence, and a remediation suggestion. Prioritise by exploitability and asset value, not by the severity of the tool\'s alert.',
                    'A useful habit: write the executive summary first. If you cannot explain the risk to a non-technical decision maker, the finding is not yet understood well enough.',
                ]],
            ],
            'Scope is not paperwork that delays the interesting part. It is the thing that makes the interesting part ethical and useful. Get the boundaries in writing, confirm ownership, model the threat, and report in language the business can act on.'
        ),
    ],
    [
        'title' => 'Passwordless Is Coming: Passkeys Explained for Normal Humans',
        'slug' => 'passkeys-explained',
        'category' => 'hacking-cybersecurity',
        'author' => 'umar',
        'date' => date('Y-m-d', strtotime('-28 days')),
        'image' => 'assets/img/blogs/cyber-2.jpg',
        'featured' => false,
        'tags' => ['passkeys', 'authentication', 'privacy'],
        'excerpt' => 'Passkeys replace passwords with cryptographic keys tied to your devices. Here is what that means, what it fixes, and what it does not.',
        'content' => $article(
            'Passwordless Is Coming',
            'Passwords have a bad century. They are reused, phished, leaked in bulk, and stored badly by companies that should know better. Passkeys are the industry\'s attempt to replace them with something that cannot be guessed or phished.',
            [
                ['The idea in one paragraph', [
                    'When you create a passkey, your device generates a key pair. The private half stays in your device\'s secure hardware; the public half goes to the website. To sign in, the site sends a challenge, your device signs it, and the site verifies. No shared secret travels over the network.',
                    'Because the private key never leaves the device and is bound to the site\'s domain, a phishing site cannot reuse it. There is nothing to type, so there is nothing to steal by typing.',
                ]],
                ['What passkeys fix', [
                    'Phishing, credential stuffing and password reuse largely stop working against a passkey-only account. Server breaches become less catastrophic because there is no password hash to crack.',
                    'Sign-in is also faster — usually a biometric prompt instead of a password manager dance.',
                ], '<blockquote>A password is a secret you share. A passkey is a key you never hand over.</blockquote>'],
                ['What they do not fix', [
                    'Passkeys do not protect a compromised device. If malware can act as you on your phone, it may be able to approve prompts. They also do not solve account recovery, which is often the weakest link in any authentication system.',
                    'And they do not remove the need for vigilance: a convincing support scam can still convince you to enrol an attacker\'s device.',
                ]],
                ['Getting started without lock-in', [
                    'Practical steps:',
                ], '<ol><li><strong>Turn on a passkey where available</strong> for high-value accounts first: email, banking, cloud storage, work identity.</li><li><strong>Keep a second factor.</strong> Most services still allow a password or recovery method — keep it strong and stored safely.</li><li><strong>Set up recovery before you need it.</strong> Print recovery codes or store them in a separate password manager vault.</li><li><strong>Register more than one device</strong> if the service supports it, so a lost phone is not a lost account.</li><li><strong>Use synced passkeys carefully.</strong> Cloud sync is convenient and makes recovery easier, but it moves trust to your cloud account — protect that account first.</li></ol>'],
                ['The honest trade-off', [
                    'Passkeys shift risk from "something you know" to "something you have" plus a local biometric. That is usually a good trade. It is not a free one: device loss, ecosystem lock-in and recovery design are real problems the industry is still solving.',
                ]],
            ],
            'Passkeys are a genuine improvement, not magic. Adopt them where the stakes are high, keep a tested recovery path, and remember that most breaches still start with a human conversation rather than a broken cipher.'
        ),
    ],
    [
        'title' => 'Inside the Phishing Economy: Why Business Email Compromise Still Works',
        'slug' => 'phishing-economy',
        'category' => 'hacking-cybersecurity',
        'author' => 'umar',
        'date' => date('Y-m-d', strtotime('-58 days')),
        'image' => 'assets/img/blogs/cyber-3.jpg',
        'featured' => false,
        'tags' => ['phishing', 'fraud', 'email-security'],
        'excerpt' => 'No exploit, no malware, no zero-day. The most expensive cybercrime in the world often runs on a polite email and a rushed finance team.',
        'content' => $article(
            'Inside the Phishing Economy',
            'Business email compromise (BEC) is unglamorous and devastating. It rarely involves code. It involves context: knowing who approves invoices, who is travelling, and when a payment run happens.',
            [
                ['The pattern', [
                    'An attacker compromises or spoofs a supplier email, or registers a lookalike domain. They reply inside an existing thread, reference a real invoice, and change only the bank details.',
                    'The victim does what good employees do: they process a payment because the message is consistent with everything around it. The failure is not stupidity. It is a process that trusts context more than verification.',
                ]],
                ['Why technical filters miss it', [
                    'Modern filters catch bulk spam well. BEC is low volume and highly targeted. The message may come from a real compromised mailbox, with correct signatures, sent to four people.',
                    'Domain lookalikes — one character swapped, a hyphen added — are cheap to register and easy to miss when the reader is answering forty emails before a deadline.',
                ], '<blockquote>BEC is not a technology problem with a technology solution. It is a verification problem.</blockquote>'],
                ['Controls that actually reduce loss', [
                    'The highest-value controls are procedural:',
                ], '<ul><li><strong>Out-of-band verification.</strong> Any change to payment details is confirmed by phone using a known number, never the contact in the email.</li><li><strong>Dual approval.</strong> Payments above a threshold require two people, one of whom must actively verify.</li><li><strong>Mailbox hardening.</strong> MFA, conditional access, and alerting on new forwarding rules — attackers love silent forwarding.</li><li><strong>Vendor change process.</strong> A documented, slow, annoying process for bank detail changes. Annoying is the point.</li><li><strong>Rehearsal.</strong> Run a small, respectful simulation. Teams that have practised are dramatically less likely to skip verification under pressure.</li></ul>'],
                ['When it happens', [
                    'Speed matters more than shame. Contact your bank immediately, request a recall, preserve headers and logs, and report to the relevant authority. The first hours are the only realistic window for recovery.',
                    'Treat the affected employee as a witness, not a culprit. A culture of blame guarantees that the next incident is discovered late.',
                ]],
                ['What to watch next', [
                    'Attackers are increasingly using legitimate cloud services, hijacked accounts and AI-written text with perfect tone. The defence does not change: verify out of band.',
                ]],
            ],
            'BEC keeps working because it exploits trust and urgency, not software. Build processes that assume a convincing email may still be a lie, and make verification the normal, praised behaviour rather than an insult.'
        ),
    ],
    [
        'title' => 'Threat Modelling for Small Teams in Five Evenings',
        'slug' => 'threat-modelling-small-teams',
        'category' => 'hacking-cybersecurity',
        'author' => 'umar',
        'date' => date('Y-m-d', strtotime('-120 days')),
        'image' => 'assets/img/blogs/cyber-4.jpg',
        'featured' => false,
        'tags' => ['threat-modelling', 'small-business', 'security'],
        'excerpt' => 'You do not need a security department to think clearly about risk. A structured five-session exercise can produce a working threat model.',
        'content' => $article(
            'Threat Modelling for Small Teams',
            'Most small teams know they should think about security and do not know where to start. Threat modelling sounds like enterprise theatre. It can be a practical, five-evening exercise.',
            [
                ['Evening one: what are we protecting?', [
                    'List the assets in plain language: customer records, payment flow, admin access, source code, reputation, uptime. For each, note who would care if it were exposed, changed or unavailable.',
                    'This alone changes conversations. Teams often discover that the thing they spend the most time securing is not the thing that would hurt most.',
                ]],
                ['Evening two: how does data move?', [
                    'Draw the system as boxes and arrows. Users, frontend, API, database, third-party services, backups. Mark where authentication happens and where data crosses a trust boundary.',
                    'The goal is not a perfect diagram. It is to find the arrows nobody owns — the webhook, the export script, the shared admin password.',
                ], '<blockquote>Every arrow you cannot explain is a question you have not asked.</blockquote>'],
                ['Evening three: who attacks us?', [
                    'Write three profiles: the opportunist (automated scanning), the insider or careless colleague, and the targeted attacker who wants money or data. For each, list the easiest path to your most valuable asset.',
                    'Keep it specific. "Hackers" is not a threat model. "A contractor with an old database password and no MFA" is.',
                ]],
                ['Evening four: what do we do first?', [
                    'Rank by likelihood × impact, then filter by cost. The usual high-value, low-cost set:',
                ], '<ul><li>MFA on email, code hosting and cloud consoles.</li><li>Remove orphaned accounts and shared credentials.</li><li>Backups that are tested, not just scheduled.</li><li>Patch the internet-facing things you forgot existed.</li><li>An incident contact list that is printed and current.</li></ul>'],
                ['Evening five: write it down and revisit', [
                    'A one-page model beats a 40-page document nobody reads: assets, actors, top five risks, owners, review date. Put it where the team can see it and review it when the architecture changes.',
                    'Threat modelling is a habit, not an artefact. The team that revisits it quarterly will out-perform the team that bought a tool and moved on.',
                ]],
                ['A closing warning', [
                    'Do not let the exercise become a wish list for expensive products. Most improvements available to small teams are configuration, process and attention — all of which are free and all of which require someone to own them.',
                ]],
            ],
            'Five evenings will not make you unhackable. They will make you deliberate. That is the difference between a team that discovers incidents from customers and a team that discovers them from its own alerts.'
        ),
    ],
];
